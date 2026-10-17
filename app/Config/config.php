<?php
declare(strict_types=1);

/**
 * Loads .env into $_ENV / getenv() and exposes a tiny env() helper.
 * No Composer dependency required for this - intentionally minimal.
 */
function issp_load_env(string $path): void
{
    if (!is_file($path)) {
        return;
    }
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $value = trim($value, "\"'");
        $_ENV[$key] = $value;
        putenv("{$key}={$value}");
    }
}

issp_load_env(BASE_PATH . '/.env');

function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? getenv($key);
    if ($value === false || $value === null) {
        return $default;
    }
    return match (strtolower((string) $value)) {
        'true' => true,
        'false' => false,
        default => $value,
    };
}
