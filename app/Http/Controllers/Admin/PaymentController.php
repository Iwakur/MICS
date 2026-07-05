<?php

/**
 * MICS source: app Http Controllers Admin PaymentController. See docs/file-reference.md for its full responsibility.
 */

namespace App\Http\Controllers\Admin;

use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReversePaymentRequest;
use App\Http\Requests\Admin\SavePaymentRequest;
use App\Models\Payment;
use App\Models\Student;
use App\Services\StudentBalanceService;
use App\Support\Money;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $month = $request->string('month', now()->format('Y-m'))->toString();
        abort_unless(preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month) === 1, 404);

        return view('admin.payments.index', [
            'month' => $month,
            'payments' => Payment::query()
                ->with(['studentMonth.student', 'validatedBy', 'reversalOf', 'reversal'])
                ->whereHas('studentMonth', fn ($query) => $query->whereDate('month_date', $month.'-01'))
                ->latest('paid_at')
                ->latest('id')
                ->paginate(25)
                ->withQueryString(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.payments.create', $this->formOptions() + [
            'payment' => null,
            'selectedMonth' => $request->string('month', now()->format('Y-m'))->toString(),
        ]);
    }

    public function store(SavePaymentRequest $request, StudentBalanceService $balances): RedirectResponse
    {
        $data = $request->validated();
        $month = CarbonImmutable::createFromFormat('!Y-m', $data['month']);
        $studentMonth = $balances->findOrCreateMonth($data['student_id'], $month);

        $payment = $studentMonth->payments()->create($this->paymentData($data));

        return to_route('admin.payments.edit', $payment)
            ->with('status', 'Payment draft created. Review it before validation.');
    }

    public function edit(Payment $payment): View
    {
        $payment->load(['studentMonth.student', 'validatedBy', 'reversalOf', 'reversal']);

        return view('admin.payments.edit', $this->formOptions() + [
            'payment' => $payment,
            'selectedMonth' => $payment->studentMonth->month_date->format('Y-m'),
        ]);
    }

    public function update(SavePaymentRequest $request, Payment $payment, StudentBalanceService $balances): RedirectResponse
    {
        $data = $request->validated();
        $month = CarbonImmutable::createFromFormat('!Y-m', $data['month']);
        $studentMonth = $balances->findOrCreateMonth($data['student_id'], $month);

        $payment->update($this->paymentData($data) + ['student_month_id' => $studentMonth->id]);

        return to_route('admin.payments.edit', $payment)->with('status', 'Payment draft updated.');
    }

    public function validatePayment(Request $request, Payment $payment, StudentBalanceService $balances): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin() && $payment->status === ReviewStatus::Draft, 403);

        DB::transaction(function () use ($request, $payment, $balances): void {
            Student::query()->whereKey($payment->studentMonth->student_id)->lockForUpdate()->firstOrFail();
            $lockedPayment = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            abort_unless($lockedPayment->status === ReviewStatus::Draft, 422, 'This payment is already validated.');

            $lockedPayment->update([
                'status' => ReviewStatus::Validated,
                'validated_by_user_id' => $request->user()->id,
                'validated_at' => now(),
            ]);
            $balances->propagateFrom($lockedPayment->studentMonth);
        }, 3);

        return to_route('admin.payments.edit', $payment)->with('status', 'Payment validated and applied to student debt.');
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        abort_unless($payment->status === ReviewStatus::Draft, 403);
        $month = $payment->studentMonth->month_date->format('Y-m');
        $payment->delete();

        return to_route('admin.payments.index', ['month' => $month])->with('status', 'Payment draft deleted.');
    }

    public function reverse(ReversePaymentRequest $request, Payment $payment, StudentBalanceService $balances): RedirectResponse
    {
        $reversal = DB::transaction(function () use ($request, $payment, $balances): Payment {
            Student::query()->whereKey($payment->studentMonth->student_id)->lockForUpdate()->firstOrFail();
            $original = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            abort_if($original->reversal()->exists(), 422, 'This payment has already been reversed.');

            $reversal = $original->studentMonth->payments()->create([
                'reversal_of_payment_id' => $original->id,
                'paid_at' => now(),
                'amount' => Money::decimal(-abs(Money::cents($original->amount))),
                'payment_method' => 'reversal',
                'status' => ReviewStatus::Validated,
                'validated_by_user_id' => $request->user()->id,
                'validated_at' => now(),
                'note' => $request->string('reason')->toString(),
            ]);

            $balances->propagateFrom($original->studentMonth);

            return $reversal;
        }, 3);

        return to_route('admin.payments.edit', $reversal)
            ->with('status', 'Payment reversed. Record a new payment if a corrected amount was received.');
    }

    private function formOptions(): array
    {
        return ['students' => Student::query()->orderBy('first_name')->orderBy('family_name')->get()];
    }

    private function paymentData(array $data): array
    {
        return collect($data)->only(['paid_at', 'amount', 'payment_method', 'note'])->all();
    }
}
