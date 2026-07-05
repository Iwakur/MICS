<?php declare(strict_types=1); ?>
<?php
$formatSqlValue = static function (mixed $value): string {
    if ($value === null) {
        return 'NULL';
    }

    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }

    return (string) $value;
};
?>
<section class="page-head">
    <div>
        <h1>SQL Console</h1>
        <p>Trusted admin tool for direct PostgreSQL work against the current app database.</p>
    </div>
</section>

<section class="panel stack">
    <p class="table-subtext">
        This is intentionally manual. The base system starts nearly empty, so this page remains useful for direct PostgreSQL maintenance when a dedicated admin workflow does not exist yet.
    </p>
    <form method="post" class="stack">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <label class="field" for="sql">
            <span>SQL</span>
            <textarea id="sql" name="sql" rows="12" class="sql-console" spellcheck="false" placeholder="SELECT * FROM users ORDER BY id;"><?= e($sql) ?></textarea>
            <?php if (isset($errors['sql'])) { ?>
                <small class="field-error"><?= e($errors['sql']) ?></small>
            <?php } ?>
        </label>
        <div class="form-actions">
            <button type="submit" class="button button-primary">Run SQL</button>
        </div>
    </form>
</section>

<?php if (is_string($executionError) && $executionError !== '') { ?>
    <section class="panel">
        <div class="alert alert-error"><?= e($executionError) ?></div>
    </section>
<?php } ?>

<?php if (is_array($result)) { ?>
    <section class="panel stack">
        <div>
            <h2>Execution Result</h2>
            <p class="table-subtext"><?= e((string) $result['message']) ?></p>
        </div>

        <?php if ($result['type'] === 'result_set') { ?>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                    <tr>
                        <?php foreach ($result['columns'] as $column) { ?>
                            <th><?= e((string) $column) ?></th>
                        <?php } ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($result['rows'] === []) { ?>
                        <tr>
                            <td colspan="<?= e((string) max(1, count($result['columns']))) ?>">No rows returned.</td>
                        </tr>
                    <?php } else { ?>
                        <?php foreach ($result['rows'] as $row) { ?>
                            <tr>
                                <?php foreach ($result['columns'] as $column) { ?>
                                    <td><?= e(array_key_exists($column, $row) ? $formatSqlValue($row[$column]) : '') ?></td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <p><?= e((string) $result['message']) ?></p>
        <?php } ?>
    </section>
<?php } ?>
