<?php
declare(strict_types=1);

namespace App\Services;

/**
 * Validates and stores an uploaded file. Public media goes to
 * public/uploads/media; gated downloads-library files go to
 * storage/uploads/resources (outside the web root - see DocumentController).
 */
class FileUploader
{
    private const ALLOWED_MIME = [
        'application/pdf', 'image/jpeg', 'image/png', 'image/webp',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    public static function storePublic(array $file, string $subDir = ''): ?string
    {
        return self::store($file, BASE_PATH . '/public/uploads/media/' . $subDir);
    }

    public static function storePrivate(array $file, string $subDir = ''): ?string
    {
        return self::store($file, BASE_PATH . '/storage/uploads/resources/' . $subDir);
    }

    private static function store(array $file, string $destinationDir): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }

        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, self::ALLOWED_MIME, true)) {
            return null;
        }

        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename  = bin2hex(random_bytes(16)) . '.' . $extension;
        $destination = $destinationDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return null;
        }

        return $destination;
    }
}
