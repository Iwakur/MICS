{{-- MICS Blade view: admin lesson-types partials form. Full responsibility is documented in docs/file-reference.md. --}}
<div class="grid gap-6 md:grid-cols-2">
    <div class="space-y-2">
        <label for="name" class="text-sm font-medium text-shell-text">Name</label>
        <input id="name" name="name" type="text" value="{{ old('name', $lessonType?->name) }}" required class="app-input">
    </div>
    <div class="space-y-2">
        <label for="duration_minutes" class="text-sm font-medium text-shell-text">Duration in Minutes</label>
        <input id="duration_minutes" name="duration_minutes" type="number" min="1" value="{{ old('duration_minutes', $lessonType?->duration_minutes) }}" required class="app-input">
    </div>
</div>

<div class="grid gap-6 md:grid-cols-2">
    <div class="space-y-2">
        <label for="lesson_price" class="text-sm font-medium text-shell-text">Student Price per Lesson</label>
        <input id="lesson_price" name="lesson_price" type="number" min="0" step="0.01" value="{{ old('lesson_price', $lessonType?->lesson_price) }}" required class="app-input">
        <p class="text-xs text-shell-muted">Amount added to the student's monthly charge for each lesson.</p>
    </div>
    <div class="space-y-2">
        <label for="teacher_share_per_lesson" class="text-sm font-medium text-shell-text">Teacher Earning per Lesson</label>
        <input id="teacher_share_per_lesson" name="teacher_share_per_lesson" type="number" min="0" step="0.01" value="{{ old('teacher_share_per_lesson', $lessonType?->teacher_share_per_lesson) }}" required class="app-input">
        <p class="text-xs text-shell-muted">Amount contributed to a dynamic teacher's salary for each lesson.</p>
    </div>
</div>

<label class="app-surface-strong flex items-center gap-3 px-4 py-3 text-sm text-shell-muted">
    <input type="checkbox" name="is_assignable" value="1" {{ old('is_assignable', $lessonType?->is_assignable ?? true) ? 'checked' : '' }} class="h-4 w-4 rounded border-shell-border bg-brand-950/70 text-shell-accent">
    <span>Available for new student assignments</span>
</label>

<div class="space-y-2">
    <label for="note" class="text-sm font-medium text-shell-text">Note</label>
    <textarea id="note" name="note" rows="4" class="app-input">{{ old('note', $lessonType?->note) }}</textarea>
</div>
