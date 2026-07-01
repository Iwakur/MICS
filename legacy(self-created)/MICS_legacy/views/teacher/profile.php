<?php declare(strict_types=1); ?>
<?php
$profileImage = profile_image_url((string) ($profile['user_profile_image_path'] ?? ''))
    ?? profile_image_url((string) ($profile['staff_profile_image_path'] ?? ''));
$displayName = person_name_from_row($profile);
$avatarFallback = strtoupper(substr($profile['first_name'] !== '' ? $profile['first_name'] : $profile['username'], 0, 1));
$errors = form_errors();
?>
<section class="page-head">
    <div>
        <h1>My Profile</h1>
        <p>Read-only account and staff identity, plus a verified password change form.</p>
    </div>
</section>

<section class="profile-layout">
    <section class="panel profile-card">
        <div class="profile-hero">
            <div class="profile-avatar-wrap">
                <?php if ($profileImage !== null): ?>
                    <img class="profile-avatar" src="<?= e($profileImage) ?>" alt="<?= e($displayName !== '' ? $displayName : $profile['username']) ?>">
                <?php else: ?>
                    <div class="profile-avatar profile-avatar-fallback"><?= e($avatarFallback) ?></div>
                <?php endif; ?>
            </div>
            <div class="profile-identity">
                <h2><?= e($displayName !== '' ? $displayName : $profile['username']) ?></h2>
                <p><?= e($profile['username']) ?></p>
                <p><?= e($profile['staff_role']) ?></p>
            </div>
        </div>

        <div class="profile-grid">
            <div class="profile-field">
                <span>Username</span>
                <strong><?= e($profile['username']) ?></strong>
            </div>
            <div class="profile-field">
                <span>Access Role</span>
                <strong><?= e(ucfirst($profile['access_role'])) ?></strong>
            </div>
            <div class="profile-field">
                <span>Staff Role</span>
                <strong><?= e($profile['staff_role']) ?></strong>
            </div>
            <div class="profile-field">
                <span>First Name</span>
                <strong><?= e($profile['first_name']) ?></strong>
            </div>
            <div class="profile-field">
                <span>Family Name</span>
                <strong><?= e((string) ($profile['family_name'] ?: 'Not set')) ?></strong>
            </div>
            <div class="profile-field">
                <span>Father Name</span>
                <strong><?= e((string) ($profile['father_name'] ?: 'Not set')) ?></strong>
            </div>
            <div class="profile-field">
                <span>Email</span>
                <strong><?= e((string) ($profile['email'] ?: 'Not set')) ?></strong>
            </div>
            <div class="profile-field">
                <span>Phone</span>
                <strong><?= e((string) ($profile['phone'] ?: 'Not set')) ?></strong>
            </div>
            <div class="profile-field">
                <span>Last Login</span>
                <strong><?= e($profile['last_login_at'] !== null ? date('d/m/Y - H:i:s', strtotime((string) $profile['last_login_at'])) : 'Never') ?></strong>
            </div>
            <div class="profile-field">
                <span>Account Created</span>
                <strong><?= e(date('d/m/Y - H:i:s', strtotime((string) $profile['created_at']))) ?></strong>
            </div>
        </div>
    </section>

    <section class="panel profile-card">
        <div class="stack">
            <h2>Change Password</h2>
            <p>Enter the current password first so the system can verify that the change is intentional.</p>
        </div>

        <form method="post" action="<?= e(app_url('teacher/profile.php')) ?>" class="stack" autocomplete="on">
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
            <input type="text" name="profile_username" value="<?= e($profile['username']) ?>" autocomplete="username" class="sr-only-input" tabindex="-1" aria-hidden="true" readonly>

            <label class="field">
                <span>Current Password</span>
                <input type="password" name="current_password" autocomplete="current-password" required>
                <?php if (isset($errors['current_password'])): ?><small class="field-error"><?= e($errors['current_password']) ?></small><?php endif; ?>
            </label>

            <label class="field">
                <span>New Password</span>
                <input type="password" name="new_password" autocomplete="new-password" required>
                <?php if (isset($errors['new_password'])): ?><small class="field-error"><?= e($errors['new_password']) ?></small><?php endif; ?>
            </label>

            <label class="field">
                <span>Confirm New Password</span>
                <input type="password" name="confirm_password" autocomplete="new-password" required>
                <?php if (isset($errors['confirm_password'])): ?><small class="field-error"><?= e($errors['confirm_password']) ?></small><?php endif; ?>
            </label>

            <div class="form-actions">
                <button type="submit" class="button button-primary">Change Password</button>
            </div>
        </form>
    </section>
</section>
