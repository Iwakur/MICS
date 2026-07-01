<?php

declare(strict_types=1);

function base_path(string $path = ''): string
{
    $full = BASE_PATH . ($path !== '' ? '/' . ltrim($path, '/') : '');

    return str_replace('\\', '/', $full);
}

function config(string $key)
{
    static $config = null;

    if ($config === null) {
        $config = require base_path('config.php');
    }

    $value = $config;

    foreach (explode('.', $key) as $segment) {
        if (! is_array($value) || ! array_key_exists($segment, $value)) {
            return null;
        }

        $value = $value[$segment];
    }

    return $value;
}

function app_url(string $path = ''): string
{
    $basePath = app_base_path();
    $cleanBase = $basePath === '' ? '' : '/' . trim($basePath, '/');
    $cleanPath = ltrim($path, '/');

    if ($cleanPath === '') {
        return $cleanBase === '' ? '/' : $cleanBase . '/';
    }

    return ($cleanBase === '' ? '' : $cleanBase) . '/' . $cleanPath;
}

function app_base_path(): string
{
    $configured = normalize_app_path((string) config('app.base_path'));
    if ($configured !== '') {
        $requestPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);

        if (! is_string($requestPath) || $requestPath === '' || request_path_matches_base($requestPath, $configured)) {
            return $configured;
        }
    }

    $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? null;
    if (! is_string($documentRoot) || $documentRoot === '') {
        return '';
    }

    $documentRootReal = realpath($documentRoot);
    $basePathReal = realpath(BASE_PATH);

    if ($documentRootReal === false || $basePathReal === false) {
        return '';
    }

    $normalizedDocumentRoot = str_replace('\\', '/', rtrim($documentRootReal, '\\/'));
    $normalizedBasePath = str_replace('\\', '/', rtrim($basePathReal, '\\/'));

    if (! str_starts_with(strtolower($normalizedBasePath), strtolower($normalizedDocumentRoot))) {
        return '';
    }

    $relative = substr($normalizedBasePath, strlen($normalizedDocumentRoot));
    if ($relative === false || $relative === '') {
        return '';
    }

    return normalize_app_path($relative);
}

function normalize_app_path(string $path): string
{
    $trimmed = trim($path);

    if ($trimmed === '' || $trimmed === '/') {
        return '';
    }

    return '/' . trim($trimmed, '/');
}

function request_path_matches_base(string $requestPath, string $basePath): bool
{
    $normalizedRequestPath = normalize_app_path($requestPath);

    if ($normalizedRequestPath === '') {
        return $basePath === '';
    }

    return $normalizedRequestPath === $basePath
        || str_starts_with($normalizedRequestPath, $basePath . '/');
}

function asset_url(string $path): string
{
    return app_url('assets/' . ltrim($path, '/'));
}

function app_timezone(): string
{
    $timezone = (string) config('app.timezone');

    return $timezone !== '' ? $timezone : 'UTC';
}

function current_app_datetime(): DateTimeImmutable
{
    return new DateTimeImmutable('now', new DateTimeZone(app_timezone()));
}

function default_user_password(): string
{
    $password = (string) config('auth.default_password');

    return $password !== '' ? $password : 'ChangeMe123!';
}

function profile_image_url(?string $path): ?string
{
    $value = trim((string) $path);

    if ($value === '') {
        return null;
    }

    if (preg_match('#^https?://#i', $value) === 1) {
        return $value;
    }

    if (str_starts_with($value, '/')) {
        return $value;
    }

    return app_url($value);
}

function redirect(string $path): never
{
    header('Location: ' . app_url($path));
    exit;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function person_full_name(?string $familyName, ?string $firstName, ?string $fatherName): string
{
    return trim(implode(' ', array_values(array_filter([
        trim((string) $familyName),
        trim((string) $firstName),
        trim((string) $fatherName),
    ], static fn (string $value): bool => $value !== ''))));
}

function person_name_from_row(array $row, string $prefix = ''): string
{
    $familyKey = $prefix === '' ? 'family_name' : $prefix . 'family_name';
    $firstKey = $prefix === '' ? 'first_name' : $prefix . 'first_name';
    $fatherKey = $prefix === '' ? 'father_name' : $prefix . 'father_name';

    return person_full_name(
        isset($row[$familyKey]) ? (string) $row[$familyKey] : null,
        isset($row[$firstKey]) ? (string) $row[$firstKey] : null,
        isset($row[$fatherKey]) ? (string) $row[$fatherKey] : null
    );
}

function csrf_token(): string
{
    if (! isset($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf_token'];
}

function verify_csrf(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION['_csrf_token'])
        && hash_equals($_SESSION['_csrf_token'], $token);
}

function flash(string $key, mixed $value = null): mixed
{
    if (func_num_args() === 2) {
        $_SESSION['_flash'][$key] = $value;

        return null;
    }

    if (! isset($_SESSION['_flash'][$key])) {
        return null;
    }

    $value = $_SESSION['_flash'][$key];
    unset($_SESSION['_flash'][$key]);

    return $value;
}

function old(?string $key = null, mixed $default = ''): mixed
{
    static $oldInputLoaded = false;
    static $oldInput = [];

    if (! $oldInputLoaded) {
        $value = flash('_old_input');
        $oldInput = is_array($value) ? $value : [];
        $oldInputLoaded = true;
    }

    if ($key === null) {
        return $oldInput;
    }

    return $oldInput[$key] ?? $default;
}

function form_errors(): array
{
    static $formErrorsLoaded = false;
    static $formErrors = [];

    if (! $formErrorsLoaded) {
        $value = flash('_form_errors');
        $formErrors = is_array($value) ? $value : [];
        $formErrorsLoaded = true;
    }

    return $formErrors;
}

function field_error(string $key): ?string
{
    $errors = form_errors();
    $value = $errors[$key] ?? null;

    return is_string($value) && $value !== '' ? $value : null;
}

function render(string $view, array $data = [], string $layout = 'guest'): void
{
    $viewFile = base_path('views/' . $view . '.php');
    $layoutFile = base_path('views/layouts/' . $layout . '.php');

    if (! is_file($viewFile) || ! is_file($layoutFile)) {
        throw new RuntimeException('View or layout not found.');
    }

    extract($data, EXTR_SKIP);
    $contentView = $viewFile;

    require $layoutFile;
}
