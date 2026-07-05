<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveExpenseCategoryRequest;
use App\Models\ExpenseCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ExpenseCategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.expense-categories.index', [
            'categories' => ExpenseCategory::query()->withCount('expenses')->orderBy('name')->paginate(25),
        ]);
    }

    public function create(): View
    {
        return view('admin.expense-categories.create', ['expenseCategory' => new ExpenseCategory]);
    }

    public function store(SaveExpenseCategoryRequest $request): RedirectResponse
    {
        ExpenseCategory::query()->create($request->validated());

        return to_route('admin.expense-categories.index')->with('status', 'Expense category created.');
    }

    public function edit(ExpenseCategory $expenseCategory): View
    {
        return view('admin.expense-categories.edit', compact('expenseCategory'));
    }

    public function update(SaveExpenseCategoryRequest $request, ExpenseCategory $expenseCategory): RedirectResponse
    {
        $expenseCategory->update($request->validated());

        return to_route('admin.expense-categories.index')->with('status', 'Expense category updated.');
    }

    public function destroy(ExpenseCategory $expenseCategory): RedirectResponse
    {
        if ($expenseCategory->expenses()->exists()) {
            $expenseCategory->update(['is_active' => false]);

            return to_route('admin.expense-categories.index')->with('status', 'Used category archived.');
        }

        $expenseCategory->delete();

        return to_route('admin.expense-categories.index')->with('status', 'Unused category deleted.');
    }
}
