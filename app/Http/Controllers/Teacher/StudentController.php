<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveStudentRequest;
use App\Models\LessonType;
use App\Models\Plan;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        return view('teacher.students.index', [
            'students' => $this->ownedStudents($request)
                ->with(['lessonType', 'plan'])
                ->orderBy('first_name')
                ->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->staffId($request);

        return view('teacher.students.create', $this->formOptions());
    }

    public function store(SaveStudentRequest $request): RedirectResponse
    {
        Student::query()->create($request->studentData($this->staffId($request)));

        return to_route('teacher.students.index')->with('status', 'Student created successfully.');
    }

    public function edit(Request $request, Student $student): View
    {
        $this->ensureOwned($request, $student);

        return view('teacher.students.edit', ['student' => $student] + $this->formOptions($student));
    }

    public function update(SaveStudentRequest $request, Student $student): RedirectResponse
    {
        $this->ensureOwned($request, $student);
        $student->update($request->studentData($this->staffId($request)));

        return to_route('teacher.students.index')->with('status', 'Student updated successfully.');
    }

    private function ownedStudents(Request $request): Builder
    {
        return Student::query()->where('staff_id', $this->staffId($request));
    }

    private function ensureOwned(Request $request, Student $student): void
    {
        abort_unless($student->staff_id === $this->staffId($request), 403);
    }

    private function staffId(Request $request): int
    {
        $staffId = $request->user()->staff_id;
        abort_if(! $staffId || ! $request->user()->staffMember?->is_active, 403, 'An active staff profile is required.');

        return $staffId;
    }

    private function formOptions(?Student $student = null): array
    {
        return [
            'lessonTypes' => LessonType::query()
                ->where('is_assignable', true)
                ->when($student?->lesson_type_id, fn (Builder $query) => $query->orWhereKey($student->lesson_type_id))
                ->orderBy('name')
                ->get(),
            'plans' => Plan::query()
                ->where('is_assignable', true)
                ->when($student?->plan_id, fn (Builder $query) => $query->orWhereKey($student->plan_id))
                ->orderBy('name')
                ->get(),
        ];
    }
}
