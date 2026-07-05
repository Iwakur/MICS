<?php declare(strict_types=1); ?>
<section class="page-head">
    <div>
        <h1>Expenses</h1>
        <p>Actual expense documents created manually or from imported outgoing bank rows.</p>
    </div>
    <a class="button button-primary" href="<?= e(app_url('admin/expense-create.php')) ?>">Create Expense</a>
</section>

<section class="panel">
    <form method="get" class="filter-bar">
        <select name="status">
            <option value="">All statuses</option>
            <?php foreach ($statuses as $expenseStatus) { ?>
                <option value="<?= e($expenseStatus) ?>"<?= $status === $expenseStatus ? ' selected' : '' ?>><?= e(ucfirst($expenseStatus)) ?></option>
            <?php } ?>
        </select>
        <select name="category_id">
            <option value="">All categories</option>
            <?php foreach ($categories as $category) { ?>
                <option value="<?= e((string) $category['id']) ?>"<?= $categoryId === (string) $category['id'] ? ' selected' : '' ?>>
                    <?= e($category['name']) ?>
                </option>
            <?php } ?>
        </select>
        <button type="submit" class="button button-ghost">Apply</button>
        <a class="button button-ghost" href="<?= e(app_url('admin/expenses.php')) ?>">Reset</a>
    </form>
</section>

<section class="panel">
    <?php if ($expenses === []) { ?>
        <p>No expense records match the current filters.</p>
    <?php } else { ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Paid From</th>
                    <th>Status</th>
                    <th>Origin</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($expenses as $expense) { ?>
                    <?php $staffName = person_name_from_row($expense); ?>
                    <tr>
                        <td>
                            <strong><?= e(date('Y-m-d H:i', strtotime((string) $expense['expense_date']))) ?></strong><br>
                            <span class="table-subtext"><?= e((string) ($expense['reason'] ?: 'No reason')) ?></span>
                        </td>
                        <td><?= e($expense['category_name']) ?><br><span class="table-subtext"><?= e($expense['category_code']) ?></span></td>
                        <td><?= e(number_format((float) $expense['amount'], 2)) ?></td>
                        <td><?= e($expense['account_code'].' - '.$expense['account_name']) ?><br><span class="table-subtext"><?= e($staffName !== '' ? $staffName : 'No staff link') ?></span></td>
                        <td><span class="status-pill status-<?= e($expense['status']) ?>"><?= e(ucfirst($expense['status'])) ?></span></td>
                        <td><?= e($expense['import_row_id'] !== null ? ('Import row #'.$expense['import_row_id']) : 'Manual') ?></td>
                        <td>
                            <div class="row-actions">
                                <a class="button button-ghost" href="<?= e(app_url('admin/expense-edit.php?id='.(int) $expense['id'])) ?>">Edit</a>
                                <?php if ($expense['status'] === 'draft') { ?>
                                    <form method="post">
                                        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="post_expense">
                                        <input type="hidden" name="expense_id" value="<?= e((string) $expense['id']) ?>">
                                        <button type="submit" class="button button-ghost">Post</button>
                                    </form>
                                <?php } ?>
                                <?php if ($expense['status'] !== 'void') { ?>
                                    <form method="post">
                                        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="void_expense">
                                        <input type="hidden" name="expense_id" value="<?= e((string) $expense['id']) ?>">
                                        <button type="submit" class="button button-ghost">Void</button>
                                    </form>
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
</section>
