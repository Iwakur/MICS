{{-- MICS Blade view: admin plans partials form. Full responsibility is documented in docs/file-reference.md. --}}
<div class="grid gap-6 md:grid-cols-2">
    <div class="space-y-2">
        <label for="name" class="text-sm font-medium text-shell-text">Name</label>
        <input id="name" name="name" type="text" value="{{ old('name', $plan?->name) }}" required class="app-input">
    </div>
    <div class="space-y-2">
        <label for="duration_minutes" class="text-sm font-medium text-shell-text">Lesson Duration in Minutes</label>
        <input id="duration_minutes" name="duration_minutes" type="number" min="1" value="{{ old('duration_minutes', $plan?->duration_minutes) }}" required class="app-input">
    </div>
</div>

<div class="grid gap-6 md:grid-cols-2">
    <div class="space-y-2">
        <label for="lesson_count" class="text-sm font-medium text-shell-text">Included Lessons</label>
        <input id="lesson_count" name="lesson_count" type="number" min="1" value="{{ old('lesson_count', $plan?->lesson_count) }}" required class="app-input">
    </div>
    <div class="space-y-2">
        <label for="lesson_price" class="text-sm font-medium text-shell-text">Reference Price per Lesson</label>
        <input id="lesson_price" name="lesson_price" type="number" min="0" step="0.01" value="{{ old('lesson_price', $plan?->lesson_price) }}" required class="app-input">
        <p class="text-xs text-shell-muted">Metadata for comparing the plan; monthly billing uses the plan price below.</p>
    </div>
</div>

<div class="grid gap-6 md:grid-cols-2">
    <div class="space-y-2">
        <label for="plan_price" class="text-sm font-medium text-shell-text">Student Monthly Plan Price</label>
        <input id="plan_price" name="plan_price" type="number" min="0" step="0.01" value="{{ old('plan_price', $plan?->plan_price) }}" required class="app-input">
    </div>
    <div class="space-y-2">
        <label for="teacher_monthly_amount" class="text-sm font-medium text-shell-text">Teacher Monthly Earning</label>
        <input id="teacher_monthly_amount" name="teacher_monthly_amount" type="number" min="0" step="0.01" value="{{ old('teacher_monthly_amount', $plan?->teacher_monthly_amount) }}" required class="app-input">
    </div>
</div>

<label class="app-surface-strong flex items-center gap-3 px-4 py-3 text-sm text-shell-muted">
    <input type="checkbox" name="is_assignable" value="1" {{ old('is_assignable', $plan?->is_assignable ?? true) ? 'checked' : '' }} class="h-4 w-4 rounded border-shell-border bg-brand-950/70 text-shell-accent">
    <span>Available for new student assignments</span>
</label>

<div class="space-y-2">
    <label for="note" class="text-sm font-medium text-shell-text">Note</label>
    <textarea id="note" name="note" rows="4" class="app-input">{{ old('note', $plan?->note) }}</textarea>
</div>
