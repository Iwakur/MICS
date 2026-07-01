<?php declare(strict_types=1); ?>
<section class="page-head">
    <div>
        <h1><?= e($pageTitle) ?></h1>
        <p><?= e($pageDescription) ?></p>
    </div>
</section>

<section class="panel">
    <h2>Starter Scope</h2>
    <p><?= e($pageNote) ?></p>
    <ul class="flat-list">
        <li>This page already uses the teacher shell and role restriction.</li>
        <li>Only own data should be shown here once CRUD is connected.</li>
        <li>Keep the teacher workspace simpler than the admin backoffice.</li>
    </ul>
</section>
