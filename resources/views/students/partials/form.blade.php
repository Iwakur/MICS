@if ($canAssignTeacher)
    <div class="space-y-2">
        <label for="staff_id" class="text-sm font-medium text-shell-text">Assigned Teacher</label>
        <select id="staff_id" name="staff_id" class="app-select" required>
            <option value="">Select teacher</option>
            @foreach ($teachers as $teacher)
                <option value="{{ $teacher->id }}" @selected((string) old('staff_id', $student?->staff_id) === (string) $teacher->id)>
                    {{ trim($teacher->first_name.' '.$teacher->family_name) }} · {{ $teacher->compensation_mode->value }}
                </option>
            @endforeach
        </select>
    </div>
@else
    <div class="app-surface-strong px-4 py-3 text-sm text-shell-muted">
        This student will remain assigned to your staff profile.
    </div>
@endif

<div class="grid gap-6 md:grid-cols-3">
    <div class="space-y-2">
        <label for="first_name" class="text-sm font-medium text-shell-text">First Name</label>
        <input id="first_name" name="first_name" type="text" value="{{ old('first_name', $student?->first_name) }}" required class="app-input">
    </div>
    <div class="space-y-2">
        <label for="family_name" class="text-sm font-medium text-shell-text">Family Name</label>
        <input id="family_name" name="family_name" type="text" value="{{ old('family_name', $student?->family_name) }}" class="app-input">
    </div>
    <div class="space-y-2">
        <label for="father_name" class="text-sm font-medium text-shell-text">Father Name</label>
        <input id="father_name" name="father_name" type="text" value="{{ old('father_name', $student?->father_name) }}" class="app-input">
    </div>
</div>

<div class="grid gap-6 md:grid-cols-2">
    <div class="space-y-2">
        <label for="email" class="text-sm font-medium text-shell-text">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $student?->email) }}" class="app-input">
    </div>
    <div class="space-y-2">
        <label for="phone" class="text-sm font-medium text-shell-text">Phone</label>
        <input id="phone" name="phone" type="text" value="{{ old('phone', $student?->phone) }}" class="app-input">
    </div>
</div>

<div class="grid gap-6 md:grid-cols-3">
    <div class="space-y-2">
        <label for="birthday" class="text-sm font-medium text-shell-text">Birthday</label>
        <input id="birthday" name="birthday" type="date" value="{{ old('birthday', optional($student?->birthday)->format('Y-m-d')) }}" class="app-input">
    </div>
    <div class="space-y-2">
        <label for="city" class="text-sm font-medium text-shell-text">City</label>
        <input id="city" name="city" type="text" value="{{ old('city', $student?->city) }}" class="app-input">
    </div>
    <div class="space-y-2">
        <label for="joined_at" class="text-sm font-medium text-shell-text">Joined Date</label>
        <input id="joined_at" name="joined_at" type="date" value="{{ old('joined_at', optional($student?->joined_at)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required class="app-input">
    </div>
</div>

<div class="grid gap-6 md:grid-cols-2">
    <div class="space-y-2">
        <label for="status" class="text-sm font-medium text-shell-text">Status</label>
        <select id="status" name="status" class="app-select">
            <option value="active" @selected(old('status', $student?->status->value ?? 'active') === 'active')>Active</option>
            <option value="paused" @selected(old('status', $student?->status->value ?? 'active') === 'paused')>Paused</option>
            @if ($canArchive)
                <option value="archived" @selected(old('status', $student?->status->value ?? 'active') === 'archived')>Archived</option>
            @endif
        </select>
    </div>
    <div class="space-y-2">
        <label for="billing_type" class="text-sm font-medium text-shell-text">Billing Type</label>
        <select id="billing_type" name="billing_type" class="app-select">
            <option value="per_lesson" @selected(old('billing_type', $student?->billing_type->value ?? 'per_lesson') === 'per_lesson')>Per lesson</option>
            <option value="plan_based" @selected(old('billing_type', $student?->billing_type->value ?? 'per_lesson') === 'plan_based')>Monthly plan</option>
        </select>
    </div>
</div>

<section class="app-surface-strong grid gap-6 p-4 md:grid-cols-2">
    <div class="space-y-2">
        <label for="lesson_type_id" class="text-sm font-medium text-shell-text">Lesson Type</label>
        <select id="lesson_type_id" name="lesson_type_id" class="app-select">
            <option value="">Not per-lesson</option>
            @foreach ($lessonTypes as $lessonType)
                <option value="{{ $lessonType->id }}" @selected((string) old('lesson_type_id', $student?->lesson_type_id) === (string) $lessonType->id)>
                    {{ $lessonType->name }} · student {{ number_format((float) $lessonType->lesson_price, 2) }} · teacher {{ number_format((float) $lessonType->teacher_share_per_lesson, 2) }}
                </option>
            @endforeach
        </select>
        <p class="text-xs text-shell-muted">Required for per-lesson billing. Lesson counts are entered separately for each month.</p>
    </div>

    <div class="grid gap-4">
        <div class="space-y-2">
            <label for="plan_id" class="text-sm font-medium text-shell-text">Plan</label>
            <select id="plan_id" name="plan_id" class="app-select">
                <option value="">Not plan-based</option>
                @foreach ($plans as $plan)
                    <option value="{{ $plan->id }}" @selected((string) old('plan_id', $student?->plan_id) === (string) $plan->id)>
                        {{ $plan->name }} · student {{ number_format((float) $plan->plan_price, 2) }} · teacher {{ number_format((float) $plan->teacher_monthly_amount, 2) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="space-y-2">
            <label for="plan_start_at" class="text-sm font-medium text-shell-text">Plan Start Date</label>
            <input id="plan_start_at" name="plan_start_at" type="date" value="{{ old('plan_start_at', optional($student?->plan_start_at)->format('Y-m-d')) }}" class="app-input">
            <p class="text-xs text-shell-muted">The start month is charged in full, regardless of the day.</p>
        </div>
    </div>
</section>

<div class="space-y-2">
    <label for="discount_amount" class="text-sm font-medium text-shell-text">Monthly Discount Amount</label>
    <input id="discount_amount" name="discount_amount" type="number" min="0" step="0.01" value="{{ old('discount_amount', $student?->discount_amount ?? 0) }}" required class="app-input">
</div>

<div class="space-y-2">
    <label for="note" class="text-sm font-medium text-shell-text">Note</label>
    <textarea id="note" name="note" rows="4" class="app-input">{{ old('note', $student?->note) }}</textarea>
</div>
