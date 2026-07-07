<?php

/**
 * MICS HUB source: app Http Controllers Admin StudentChargeController. See docs/file-reference.md for its full responsibility.
 */

namespace App\Http\Controllers\Admin;

use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateStudentChargeRequest;
use App\Models\StudentMonth;
use App\Services\StudentBalanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StudentChargeController extends Controller
{
    public function index(Request $request): View
    {
        $month = $request->string('month', now()->format('Y-m'))->toString();
        abort_unless(preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month) === 1, 404);

        return view('admin.student-charges.index', [
            'month' => $month,
            'studentMonths' => StudentMonth::query()->with(['student.teacher', 'adjustedBy'])
                ->whereDate('month_date', $month.'-01')->orderBy('student_id')->paginate(25)->withQueryString(),
        ]);
    }

    public function edit(StudentMonth $studentMonth): View
    {
        return view('admin.student-charges.edit', ['studentMonth' => $studentMonth->load(['student', 'validatedBy'])]);
    }

    public function update(UpdateStudentChargeRequest $request, StudentMonth $studentMonth, StudentBalanceService $balances): RedirectResponse
    {
        DB::transaction(function () use ($request, $studentMonth, $balances): void {
            $data = $request->validated() + ['adjusted_by_user_id' => $request->user()->id];

            if ($data['status'] === ReviewStatus::Validated->value) {
                $data['validated_by_user_id'] = $request->user()->id;
                $data['validated_at'] = now();
            }

            $studentMonth->update($data);
            $balances->propagateFrom($studentMonth, createNextMonth: true);
        });

        return to_route('admin.student-charges.index', ['month' => $studentMonth->month_date->format('Y-m')])
            ->with('status', __('finance.charge_updated'));
    }
}
