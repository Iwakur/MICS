<?php declare(strict_types=1); ?>
<section class="page-head">
    <div>
        <h1>My Students</h1>
        <p>Your assigned students only, with direct edit and status actions.</p>
    </div>
    <a class="button button-primary" href="<?= e(app_url('teacher/add-student.php')) ?>">Add Student</a>
</section>

<section class="panel">
    <form method="get" class="filter-bar">
        <input type="search" name="q" value="<?= e($search) ?>" placeholder="Search by name, phone, or email">
        <select name="status">
            <option value="">All statuses</option>
            <?php foreach ($statuses as $studentStatus) { ?>
                <option value="<?= e($studentStatus) ?>"<?= $status === $studentStatus ? ' selected' : '' ?>><?= e(ucfirst($studentStatus)) ?></option>
            <?php } ?>
        </select>
        <button type="submit" class="button button-ghost">Apply</button>
    </form>
</section>

<section class="panel">
    <?php if ($students === []) { ?>
        <p>No students match the current filters.</p>
    <?php } else { ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                <tr>
                    <th>Student</th>
                    <th>Plan</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($students as $student) { ?>
                    <?php $studentName = person_name_from_row($student); ?>
                    <tr>
                        <td>
                            <strong><?= e($studentName) ?></strong><br>
                            <span class="table-subtext"><?= e((string) ($student['phone'] ?: $student['email'] ?: 'No contact')) ?></span>
                        </td>
                        <td><?= e($student['plan_name']) ?></td>
                        <td><span class="status-pill status-<?= e($student['status']) ?>"><?= e(ucfirst($student['status'])) ?></span></td>
                        <td><?= e(date('Y-m-d H:i', strtotime((string) $student['joined_at']))) ?></td>
                        <td>
                            <div class="row-actions">
                                <a class="button button-ghost" href="<?= e(app_url('teacher/student-edit.php?id='.(int) $student['id'])) ?>">Edit</a>
                                <form method="post">
                                    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="student_id" value="<?= e((string) $student['id']) ?>">
                                    <input type="hidden" name="status" value="<?= e($student['status'] === 'archived' ? 'active' : 'archived') ?>">
                                    <button type="submit" class="button button-ghost"><?= $student['status'] === 'archived' ? 'Activate' : 'Archive' ?></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
</section>
