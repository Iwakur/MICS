<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StudentBillingType;
use App\Enums\StudentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaveStudentRequest;
use App\Models\LessonType;
use App\Models\Plan;
use App\Models\Staff;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $students = Student::query()
            ->with(['teacher', 'lessonType', 'plan'])
            ->when($request->filled('search'), fn (Builder $query) => $query->where(function (Builder $query) use ($request): void {
                $search = '%'.$request->string('search')->trim().'%';
                $query->where('first_name', 'like', $search)
                    ->orWhere('family_name', 'like', $search)
                    ->orWhere('phone', 'like', $search);
            }))
            ->when($request->enum('status', StudentStatus::class), fn (Builder $query, StudentStatus $status) => $query->where('status', $status->value))
            ->when($request->enum('billing_type', StudentBillingType::class), fn (Builder $query, StudentBillingType $type) => $query->where('billing_type', $type->value))
            ->when($request->integer('staff_id'), fn (Builder $query, int $staffId) => $query->where('staff_id', $staffId))
            ->orderByDesc('status')
            ->orderBy('first_name')
            ->get();

        return view('admin.students.index', [
            'students' => $students,
            'teachers' => $this->teachingStaff()->orderBy('first_name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.students.create', $this->formOptions());
    }

    public function store(SaveStudentRequest $request): RedirectResponse
    {
        Student::query()->create($request->studentData());

        return to_route('admin.students.index')->with('status', 'Student created successfully.');
    }

    public function edit(Student $student): View
    {
        return view('admin.students.edit', ['student' => $student] + $this->formOptions($student));
    }

    public function update(SaveStudentRequest $request, Student $student): RedirectResponse
    {
        $student->update($request->studentData());

        return to_route('admin.students.index')->with('status', 'Student updated successfully.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->update(['status' => StudentStatus::Archived]);

        return to_route('admin.students.index')->with('status', 'Student archived successfully.');
    }

    private function formOptions(?Student $student = null): array
    {
        return [
            'teachers' => $this->teachingStaff()
                ->when($student, fn (Builder $query) => $query->orWhere($query->getModel()->getQualifiedKeyName(), $student->staff_id))
                ->orderBy('first_name')
                ->get(),
            'lessonTypes' => LessonType::query()
                ->where('is_assignable', true)
                ->when($student?->lesson_type_id, fn (Builder $query) => $query->orWhere($query->getModel()->getQualifiedKeyName(), $student->lesson_type_id))
                ->orderBy('name')
                ->get(),
            'plans' => Plan::query()
                ->where('is_assignable', true)
                ->when($student?->plan_id, fn (Builder $query) => $query->orWhere($query->getModel()->getQualifiedKeyName(), $student->plan_id))
                ->orderBy('name')
                ->get(),
        ];
    }

    private function teachingStaff(): Builder
    {
        return Staff::query()
            ->where('is_active', true)
            ->whereHas('role', fn (Builder $query) => $query->where('can_teach', true));
    }
}
