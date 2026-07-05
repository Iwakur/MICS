{{-- MICS Blade view: admin staff-roles partials form. Full responsibility is documented in docs/file-reference.md. --}}
<div class="space-y-2">
    <label for="name" class="text-sm font-medium text-shell-text">Role Name</label>
    <input id="name" name="name" type="text" value="{{ old('name', $staffRole?->name) }}" required class="app-input">
</div>

<div class="grid gap-4 md:grid-cols-2">
    <label class="app-surface-strong flex items-center gap-3 px-4 py-3 text-sm text-shell-muted">
        <input type="checkbox" name="can_teach" value="1" {{ old('can_teach', $staffRole?->can_teach ?? false) ? 'checked' : '' }} class="h-4 w-4 rounded border-shell-border bg-brand-950/70 text-shell-accent">
        <span><strong class="text-shell-text">Can teach</strong><br>Staff with this role may receive student assignments.</span>
    </label>
    <label class="app-surface-strong flex items-center gap-3 px-4 py-3 text-sm text-shell-muted">
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $staffRole?->is_active ?? true) ? 'checked' : '' }} class="h-4 w-4 rounded border-shell-border bg-brand-950/70 text-shell-accent">
        <span><strong class="text-shell-text">Active role</strong><br>Available when creating or editing staff.</span>
    </label>
</div>

<div class="space-y-2">
    <label for="note" class="text-sm font-medium text-shell-text">Note</label>
    <textarea id="note" name="note" rows="4" class="app-input">{{ old('note', $staffRole?->note) }}</textarea>
</div>
