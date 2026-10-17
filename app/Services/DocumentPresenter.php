<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Media;
use App\Models\ResourceCategory;

/**
 * Adds the display-only fields the stakeholder portal shows next to each
 * document (category name, file type, human-readable size) so the
 * dashboard and the documents page format them identically.
 */
class DocumentPresenter
{
    /**
     * @param array<int, array<string, mixed>> $documents rows from Resource::visibleTo()
     * @return array<int, array<string, mixed>>
     */
    public static function enrich(array $documents): array
    {
        if (empty($documents)) {
            return [];
        }

        $categoryNames = [];
        foreach (ResourceCategory::all('name ASC') as $category) {
            $categoryNames[(int) $category['id']] = $category['name'];
        }

        foreach ($documents as &$doc) {
            $media = !empty($doc['file_media_id']) ? Media::find((int) $doc['file_media_id']) : null;
            $doc['category_name'] = $categoryNames[(int) ($doc['category_id'] ?? 0)] ?? null;
            $doc['file_type']     = self::typeLabel($media['mime_type'] ?? null);
            $doc['file_size']     = isset($media['file_size_bytes']) ? self::formatBytes((int) $media['file_size_bytes']) : null;
        }
        unset($doc);

        return $documents;
    }

    public static function typeLabel(?string $mime): string
    {
        return match (true) {
            $mime === 'application/pdf'                         => 'PDF',
            $mime === 'application/msword',
            str_contains((string) $mime, 'wordprocessingml')    => 'DOC',
            str_starts_with((string) $mime, 'image/')           => 'IMG',
            default                                             => 'FILE',
        };
    }

    public static function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        }
        return max(1, (int) round($bytes / 1024)) . ' KB';
    }
}
