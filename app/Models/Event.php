<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Event extends Model
{
    protected static string $table = 'events';
    protected static bool $softDeletes = true;

    public static function upcoming(): array
    {
        $db = \App\Core\Database::connection();
        $sql = "SELECT * FROM events WHERE status = 'published' AND deleted_at IS NULL
                 AND starts_at >= NOW() ORDER BY starts_at ASC";
        return $db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public static function findPublishedBySlug(string $slug): ?array
    {
        $db   = \App\Core\Database::connection();
        $stmt = $db->prepare("SELECT * FROM events WHERE slug = ? AND status = 'published' LIMIT 1");
        $stmt->bind_param('s', $slug);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }
}
