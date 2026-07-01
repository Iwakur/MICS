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
        <li>This page already uses the admin shell, role gate, and shared navigation.</li>
        <li>The next implementation step is the actual CRUD workflow and filters for this module.</li>
        <li>Keep business documents and accounting posting separated when you build the final flow.</li>
    </ul>
</section>
