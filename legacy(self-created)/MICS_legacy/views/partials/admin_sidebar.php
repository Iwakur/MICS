<?php declare(strict_types=1); ?>
<aside class="sidebar sidebar-admin">
    <nav class="nav-group">
        <div class="nav-label">Main</div>
        <a href="<?= e(app_url('admin/dashboard.php')) ?>">Dashboard</a>
        <a href="<?= e(app_url('admin/plans.php')) ?>">Plans</a>
        <a href="<?= e(app_url('admin/students.php')) ?>">Students</a>
        <a href="<?= e(app_url('admin/staff.php')) ?>">Staff</a>
    </nav>
    <nav class="nav-group">
        <div class="nav-label">Finance</div>
        <a href="<?= e(app_url('admin/imports.php')) ?>">Import Hub</a>
        <a href="<?= e(app_url('admin/payments.php')) ?>">Payments</a>
        <a href="<?= e(app_url('admin/expenses.php')) ?>">Expenses</a>
        <a href="<?= e(app_url('admin/payouts.php')) ?>">Staff Payouts</a>
        <a href="<?= e(app_url('admin/accounts.php')) ?>">Accounts</a>
        <a href="<?= e(app_url('admin/journal.php')) ?>">Journal</a>
    </nav>
    <nav class="nav-group">
        <div class="nav-label">System</div>
        <a href="<?= e(app_url('admin/users.php')) ?>">Users</a>
        <a href="<?= e(app_url('admin/settings.php')) ?>">Settings</a>
        <a href="<?= e(app_url('logout.php')) ?>">Logout</a>
    </nav>
</aside>
