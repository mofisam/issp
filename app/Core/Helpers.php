<?php
declare(strict_types=1);

use App\Core\Session;

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function asset(string $path): string
{
    return '/assets/' . ltrim($path, '/');
}

function url(string $path = ''): string
{
    return '/' . ltrim($path, '/');
}

function old(string $key, string $default = ''): string
{
    return e($_SESSION['_old'][$key] ?? $default);
}

function flash_get(string $key): ?string
{
    return Session::flash($key);
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

function format_date(?string $datetime, string $format = 'd M Y'): string
{
    if (!$datetime) {
        return '';
    }
    return date($format, strtotime($datetime));
}

/**
 * Resolve a media row's absolute server path to a public URL.
 * mysqli can hand back foreign-key ids as strings rather than ints, so this
 * accepts either. Returns null when there's no media or the row is missing.
 */
function media_url(int|string|null $mediaId): ?string
{
    if (!$mediaId) {
        return null;
    }
    $media = \App\Models\Media::find((int) $mediaId);
    if (!$media || empty($media['file_path'])) {
        return null;
    }
    return '/' . ltrim(str_replace(BASE_PATH . '/public/', '', $media['file_path']), '/');
}
