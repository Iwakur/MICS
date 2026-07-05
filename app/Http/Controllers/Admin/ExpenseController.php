<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveExpenseRequest;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Staff;
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
                ->orderByDesc('month_date')->orderByDesc('id')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.expenses.create', $this->formOptions());
    }

    public function store(SaveExpenseRequest $request): RedirectResponse
    {
        Expense::query()->create($request->validated() + ['is_auto_generated' => false]);

        return to_route('admin.expenses.index')->with('status', 'Expense created successfully.');
    }

    public function edit(Expense $expense): View
    {
        $expense->load(['salarySources.student', 'category', 'staffMember']);

        return view('admin.expenses.edit', ['expense' => $expense] + $this->formOptions());
    }

    public function update(SaveExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $expense->update($request->validated());

        return to_route('admin.expenses.index')->with('status', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        abort_if($expense->is_auto_generated || $expense->status === ReviewStatus::Validated, 403);
        $expense->delete();

        return to_route('admin.expenses.index')->with('status', 'Expense deleted successfully.');
    }

    private function formOptions(): array
    {
        return [
            'categories' => ExpenseCategory::query()->orderBy('name')->get(),
            'staffMembers' => Staff::query()->orderBy('first_name')->get(),
        ];
    }
}
