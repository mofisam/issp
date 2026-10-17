<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Page extends Model
{
    protected static string $table = 'pages';

    public static function published(): array
    {
        $db  = \App\Core\Database::connection();
        $sql = "SELECT * FROM pages WHERE status = 'published' AND deleted_at IS NULL ORDER BY sort_order ASC";
        return $db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public static function findPublishedBySlug(string $slug): ?array
    {
        $db   = \App\Core\Database::connection();
        $stmt = $db->prepare("SELECT * FROM pages WHERE slug = ? AND status = 'published' AND deleted_at IS NULL LIMIT 1");
        $stmt->bind_param('s', $slug);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }
}
