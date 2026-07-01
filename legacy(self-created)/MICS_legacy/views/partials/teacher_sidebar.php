<?php declare(strict_types=1); ?>
<aside class="sidebar sidebar-teacher">
    <nav class="nav-group">
        <div class="nav-label">Teacher</div>
        <a href="<?= e(app_url('teacher/dashboard.php')) ?>">Dashboard</a>
        <a href="<?= e(app_url('teacher/students.php')) ?>">My Students</a>
        <!-- <a href="<?= e(app_url('teacher/add-student.php')) ?>">Add Student</a> -->
        <a href="<?= e(app_url('teacher/payouts.php')) ?>">My Payouts</a>
        <a href="<?= e(app_url('logout.php')) ?>">Logout</a>
    </nav>
</aside>
