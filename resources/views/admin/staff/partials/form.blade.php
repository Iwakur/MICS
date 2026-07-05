@php($staffRoleId = old('staff_role_id', $staffMember?->staff_role_id))
@php($linkedUser = $staffMember?->user)

<div class="grid gap-6 md:grid-cols-3">
    <div class="space-y-2">
        <label for="staff_role_id" class="text-sm font-medium text-shell-text">Staff Role</label>
        <select id="staff_role_id" name="staff_role_id" class="app-select">
            <option value="">Select role</option>
            @foreach ($staffRoles as $staffRole)
                <option value="{{ $staffRole->id }}" @selected((string) $staffRoleId === (string) $staffRole->id)>{{ $staffRole->name }}</option>
            @endforeach
        </select>
        <p class="text-xs text-shell-muted">Roles are managed separately. Only teaching-capable roles may receive students.</p>
    </div>

    <div class="space-y-2">
        <label for="is_active" class="text-sm font-medium text-shell-text">Status</label>
        <select id="is_active" name="is_active" class="app-select">
            <option value="1" @selected(old('is_active', $staffMember?->is_active ?? true))>active</option>
            <option value="0" @selected(! old('is_active', $staffMember?->is_active ?? true))>inactive</option>
        </select>
    </div>
</div>

<div class="grid gap-6 md:grid-cols-3">
    <div class="space-y-2">
        <label for="first_name" class="text-sm font-medium text-shell-text">First Name</label>
        <input id="first_name" name="first_name" type="text" value="{{ old('first_name', $staffMember?->first_name) }}" required class="app-input">
    </div>
    <div class="space-y-2">
        <label for="family_name" class="text-sm font-medium text-shell-text">Family Name</label>
        <input id="family_name" name="family_name" type="text" value="{{ old('family_name', $staffMember?->family_name) }}" class="app-input">
    </div>
    <div class="space-y-2">
        <label for="father_name" class="text-sm font-medium text-shell-text">Father Name</label>
        <input id="father_name" name="father_name" type="text" value="{{ old('father_name', $staffMember?->father_name) }}" class="app-input">
    </div>
</div>

<div class="grid gap-6 md:grid-cols-2">
    <div class="space-y-2">
        <label for="email" class="text-sm font-medium text-shell-text">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $staffMember?->email) }}" class="app-input">
    </div>
    <div class="space-y-2">
        <label for="phone" class="text-sm font-medium text-shell-text">Phone</label>
        <input id="phone" name="phone" type="text" value="{{ old('phone', $staffMember?->phone) }}" class="app-input">
    </div>
</div>

<div class="grid gap-6 md:grid-cols-2">
    <div class="space-y-2">
        <label for="birthday" class="text-sm font-medium text-shell-text">Birthday</label>
        <input id="birthday" name="birthday" type="date" value="{{ old('birthday', optional($staffMember?->birthday)->format('Y-m-d')) }}" class="app-input">
    </div>
    <div class="space-y-2">
        <label for="city" class="text-sm font-medium text-shell-text">City</label>
        <input id="city" name="city" type="text" value="{{ old('city', $staffMember?->city) }}" class="app-input">
    </div>
</div>

<div class="grid gap-6 md:grid-cols-2">
    <div class="space-y-2">
        <label for="payout_card_number" class="text-sm font-medium text-shell-text">Payout Card Number</label>
        <input id="payout_card_number" name="payout_card_number" type="text" value="{{ old('payout_card_number', $staffMember?->payout_card_number) }}" class="app-input">
    </div>
    <div class="space-y-2">
        <label for="compensation_mode" class="text-sm font-medium text-shell-text">Compensation Mode</label>
        <select id="compensation_mode" name="compensation_mode" class="app-select">
            <option value="fixed" @selected(old('compensation_mode', $staffMember?->compensation_mode->value ?? 'fixed') === 'fixed')>Fixed salary</option>
            <option value="dynamic" @selected(old('compensation_mode', $staffMember?->compensation_mode->value ?? 'fixed') === 'dynamic')>Dynamic by students</option>
        </select>
    </div>
    <div class="space-y-2">
        <label for="salary_amount" class="text-sm font-medium text-shell-text">Fixed Salary Amount</label>
        <input id="salary_amount" name="salary_amount" type="number" step="0.01" min="0" value="{{ old('salary_amount', $staffMember?->salary_amount) }}" class="app-input">
        <p class="text-xs text-shell-muted">Required for fixed staff. Dynamic staff use lesson and plan earnings.</p>
    </div>
</div>

<div class="space-y-2">
    <label for="note" class="text-sm font-medium text-shell-text">Note</label>
    <textarea id="note" name="note" rows="4" class="app-input">{{ old('note', $staffMember?->note) }}</textarea>
</div>

<section class="app-surface-strong p-4">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-semibold text-shell-text">Login Account</h3>
            <p class="mt-1 text-sm text-shell-muted">
                Link an account created in User Management. Each account can belong to only one staff member.
            </p>
        </div>
        @if ($linkedUser)
            <span class="app-badge-active">{{ $linkedUser->username }}</span>
        @else
            <span class="app-badge-inactive">No linked account</span>
        @endif
    </div>

    <div class="mt-4 space-y-2">
        <label for="user_id" class="text-sm font-medium text-shell-text">Linked Account</label>
        <select id="user_id" name="user_id" class="app-select">
            <option value="">No linked account</option>
            @foreach ($availableUsers as $availableUser)
                <option value="{{ $availableUser->id }}" @selected((string) old('user_id', $linkedUser?->id) === (string) $availableUser->id)>
                    {{ $availableUser->username }} · {{ $availableUser->email }} · {{ $availableUser->role->value }}
                </option>
            @endforeach
        </select>
        <p class="text-xs text-shell-muted">
            Missing an account? Create it in User Management first, then return here to link it.
        </p>
    </div>
</section>
