<?php

/**
 * MICS source: app Http Controllers MonthlyLessonCountController. See docs/file-reference.md for its full responsibility.
 */

namespace App\Http\Controllers;

use App\Enums\BillingMonthStatus;
use App\Enums\StudentBillingType;
use App\Enums\StudentStatus;
use App\Http\Requests\SaveMonthlyLessonCountsRequest;
use App\Models\BillingMonth;
use App\Models\Student;
use App\Models\StudentMonth;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MonthlyLessonCountController extends Controller
{
    public function index(Request $request): View
    {
        $month = $this->selectedMonth($request);
        $this->ensureTeachingProfile($request);

        return view('lesson-counts.index', [
            'month' => $month,
            'billingMonth' => BillingMonth::query()->whereDate('month_date', $month)->first(),
            'students' => $this->eligibleStudents($request, $month)->get(),
            'routePrefix' => $request->user()->isAdmin() ? 'admin' : 'teacher',
        ]);
    }

    public function update(SaveMonthlyLessonCountsRequest $request): RedirectResponse
    {
        $this->ensureTeachingProfile($request);
        $this->saveCounts($request);
        $prefix = $request->user()->isAdmin() ? 'admin' : 'teacher';

        return to_route($prefix.'.lesson-counts.index', ['month' => $request->string('month')->toString()])
            ->with('status', 'Lesson counts saved successfully.');
    }

    private function saveCounts(SaveMonthlyLessonCountsRequest $request): void
    {
        DB::transaction(function () use ($request): void {
            $billingMonth = BillingMonth::query()->firstOrCreate(['month_date' => $request->monthDate()]);
            $billingMonth = BillingMonth::query()->lockForUpdate()->findOrFail($billingMonth->id);
            abort_if($billingMonth->status === BillingMonthStatus::Closed, 422, 'This month is closed.');

            foreach ($request->lessonCounts() as $studentId => $count) {
                StudentMonth::query()->updateOrCreate(
                    ['student_id' => $studentId, 'month_date' => $request->monthDate()],
                    ['lesson_count' => $count],
                );
            }
        });
    }

    private function eligibleStudents(Request $request, CarbonImmutable $month): Builder
    {
        return Student::query()
            ->with(['teacher:id,first_name,family_name', 'lessonType:id,name,lesson_price,teacher_share_per_lesson'])
            ->with(['months' => fn ($query) => $query->whereDate('month_date', $month)])
            ->where('status', StudentStatus::Active)
            ->where('billing_type', StudentBillingType::PerLesson)
            ->whereDate('joined_at', '<=', $month->endOfMonth())
            ->when(! $request->user()->isAdmin(), fn (Builder $query) => $query->where('staff_id', $request->user()->staff_id))
            ->orderBy('first_name')
            ->orderBy('family_name');
    }

    private function selectedMonth(Request $request): CarbonImmutable
    {
        $month = $request->string('month', now()->format('Y-m'))->toString();
        abort_unless(preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month) === 1, 404);

        return CarbonImmutable::createFromFormat('!Y-m', $month);
    }

    private function ensureTeachingProfile(Request $request): void
    {
        if ($request->user()->isAdmin()) {
            return;
        }

        $staff = $request->user()->staffMember;
        abort_if(! $staff?->is_active || ! $staff->role?->can_teach, 403, 'An active teaching staff profile is required.');
    }
}
