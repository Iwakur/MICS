<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveLessonTypeRequest;
use App\Models\LessonType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LessonTypeController extends Controller
{
    public function index(): View
    {
        return view('admin.lesson-types.index', [
            'lessonTypes' => LessonType::query()->withCount('students')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.lesson-types.create');
    }

    public function store(SaveLessonTypeRequest $request): RedirectResponse
    {
        LessonType::query()->create($request->validated());

        return to_route('admin.lesson-types.index')->with('status', 'Lesson type created successfully.');
    }

    public function edit(LessonType $lessonType): View
    {
        return view('admin.lesson-types.edit', ['lessonType' => $lessonType]);
    }

    public function update(SaveLessonTypeRequest $request, LessonType $lessonType): RedirectResponse
    {
        $lessonType->update($request->validated());

        return to_route('admin.lesson-types.index')->with('status', 'Lesson type updated successfully.');
    }

    public function destroy(LessonType $lessonType): RedirectResponse
    {
        $lessonType->update(['is_assignable' => false]);

        return to_route('admin.lesson-types.index')->with('status', 'Lesson type archived successfully.');
    }
}
