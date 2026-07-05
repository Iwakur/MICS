<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateStudentChargeRequest;
use App\Models\StudentMonth;
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
                ->whereDate('month_date', $month.'-01')->orderBy('student_id')->get(),
        ]);
    }

    public function edit(StudentMonth $studentMonth): View
    {
        return view('admin.student-charges.edit', ['studentMonth' => $studentMonth->load('student')]);
    }

    public function update(UpdateStudentChargeRequest $request, StudentMonth $studentMonth): RedirectResponse
    {
        DB::transaction(function () use ($request, $studentMonth): void {
            $studentMonth->update($request->validated() + ['adjusted_by_user_id' => $request->user()->id]);
            StudentMonth::query()->updateOrCreate(
                ['student_id' => $studentMonth->student_id, 'month_date' => $studentMonth->month_date->copy()->addMonth()],
                ['opening_balance' => $studentMonth->fresh()->closingBalance()],
            );
        });

        return to_route('admin.student-charges.index', ['month' => $studentMonth->month_date->format('Y-m')])
            ->with('status', 'Student charge updated successfully.');
    }
}
