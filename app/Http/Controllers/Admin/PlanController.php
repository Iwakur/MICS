<?php

/**
 * MICS source: app Http Controllers Admin PlanController. See docs/file-reference.md for its full responsibility.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SavePlanRequest;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(): View
    {
        return view('admin.plans.index', [
            'plans' => Plan::query()->withCount('students')->orderBy('name')->paginate(25),
        ]);
    }

    public function create(): View
    {
        return view('admin.plans.create');
    }

    public function store(SavePlanRequest $request): RedirectResponse
    {
        Plan::query()->create($request->validated());

        return to_route('admin.plans.index')->with('status', 'Plan created successfully.');
    }

    public function edit(Plan $plan): View
    {
        return view('admin.plans.edit', ['plan' => $plan]);
    }

    public function update(SavePlanRequest $request, Plan $plan): RedirectResponse
    {
        $plan->update($request->validated());

        return to_route('admin.plans.index')->with('status', 'Plan updated successfully.');
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        $plan->update(['is_assignable' => false]);

        return to_route('admin.plans.index')->with('status', 'Plan archived successfully.');
    }
}
