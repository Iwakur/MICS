<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReconcileBankMonthRequest;
use App\Http\Requests\Admin\ReopenBankMonthRequest;
use App\Models\BankMonth;
use App\Services\BankReconciliationService;
use App\Support\Money;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BankMonthController extends Controller
{
    public function index(Request $request, BankReconciliationService $service): View
    {
        $monthValue = $request->string('month', now()->format('Y-m'))->toString();
        abort_unless(preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $monthValue) === 1, 404);
        $month = CarbonImmutable::createFromFormat('!Y-m', $monthValue);
        $bankMonth = BankMonth::query()->with(['reconciledBy', 'events.user'])->whereDate('month_date', $month)->first();

        return view('admin.bank-months.index', [
            'month' => $monthValue,
            'totals' => $service->totals($month),
            'bankMonth' => $bankMonth,
            'variance' => $bankMonth
                ? Money::display(Money::cents($bankMonth->closing_balance) - Money::cents($bankMonth->expected_closing_balance))
                : null,
        ]);
    }

    public function store(ReconcileBankMonthRequest $request, BankReconciliationService $service): RedirectResponse
    {
        $service->reconcile(
            CarbonImmutable::createFromFormat('!Y-m', $request->string('month')->toString()),
            $request->string('closing_balance')->toString(),
            $request->input('variance_reason'),
            $request->input('note'),
            $request->user(),
        );

        return to_route('admin.bank-months.index', ['month' => $request->string('month')])->with('status', __('finance.bank_reconciled'));
    }

    public function reopen(ReopenBankMonthRequest $request, BankMonth $bankMonth, BankReconciliationService $service): RedirectResponse
    {
        $service->reopen($bankMonth, $request->string('reason')->toString(), $request->user());

        return to_route('admin.bank-months.index', ['month' => CarbonImmutable::parse($bankMonth->month_date)->format('Y-m')])->with('status', __('finance.bank_reopened'));
    }
}
