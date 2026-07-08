<?php

/**
 * MICS HUB source: app Http Controllers Admin ExpenseController. See docs/file-reference.md for its full responsibility.
 */

namespace App\Http\Controllers\Admin;

use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveExpenseRequest;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Staff;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.expenses.index', [
            'expenses' => Expense::query()->with(['category', 'staffMember'])->withCount('salarySources')
                ->when($request->filled('month'), fn ($query) => $query->whereDate('month_date', $request->string('month').'-01'))
                ->orderByDesc('month_date')->orderByDesc('id')->paginate(25)->withQueryString(),
        ]);
    }

    public function create(Request $request): View
    {
        $month = $request->string('month', now()->format('Y-m'))->toString();
        abort_unless(preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month) === 1, 404);

        return view('admin.expenses.create', $this->formOptions() + ['selectedMonth' => $month]);
    }

    public function store(SaveExpenseRequest $request): RedirectResponse
    {
        Expense::query()->create($this->auditedData($request) + ['is_auto_generated' => false]);

        return to_route('admin.expenses.index', ['month' => $this->requestMonth($request)])->with('status', __('finance.expense_created'));
    }

    public function edit(Expense $expense): View
    {
        $expense->load(['salarySources.student', 'category', 'staffMember', 'validatedBy']);

        return view('admin.expenses.edit', ['expense' => $expense] + $this->formOptions());
    }

    public function update(SaveExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $expense->update($this->auditedData($request));

        return to_route('admin.expenses.index', ['month' => $this->requestMonth($request)])->with('status', __('finance.expense_updated'));
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        abort_if($expense->is_auto_generated || $expense->status === ReviewStatus::Validated, 403);
        $expense->delete();

        $month = CarbonImmutable::parse((string) $expense->month_date)->format('Y-m');

        return to_route('admin.expenses.index', ['month' => $month])->with('status', __('finance.expense_deleted'));
    }

    private function formOptions(): array
    {
        return [
            'categories' => ExpenseCategory::query()->where('is_active', true)->orderBy('name')->get(),
            'staffMembers' => Staff::query()->orderBy('first_name')->get(),
        ];
    }

    private function auditedData(SaveExpenseRequest $request): array
    {
        $data = $request->validated();

        if ($data['status'] === ReviewStatus::Validated->value) {
            $data['validated_by_user_id'] = $request->user()->id;
            $data['validated_at'] = now();
        }

        return $data;
    }

    private function requestMonth(SaveExpenseRequest $request): string
    {
        return CarbonImmutable::parse((string) $request->validated('month_date'))->format('Y-m');
    }
}
