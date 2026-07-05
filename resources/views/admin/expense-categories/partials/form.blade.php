<div class="grid gap-5">
    <div><label for="name" class="text-sm text-shell-muted">Name</label><input id="name" name="name" value="{{ old('name', $expenseCategory->name) }}" class="app-input mt-2" maxlength="100" required></div>
    <div><label for="note" class="text-sm text-shell-muted">Note</label><textarea id="note" name="note" class="app-input mt-2" rows="4">{{ old('note', $expenseCategory->note) }}</textarea></div>
    <label class="flex items-center gap-3 text-sm text-shell-muted"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $expenseCategory->exists ? $expenseCategory->is_active : true))> Available for new expenses</label>
</div>
