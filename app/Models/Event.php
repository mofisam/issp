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

    /** Published events that have already started, newest first. */
    public static function pastPaginated(int $page = 1, int $perPage = 12): array
    {
        $db     = \App\Core\Database::connection();
        $offset = ($page - 1) * $perPage;
        $stmt   = $db->prepare(
            "SELECT * FROM events WHERE status IN ('published','completed') AND deleted_at IS NULL
                AND starts_at < NOW() ORDER BY starts_at DESC LIMIT ? OFFSET ?"
        );
        $stmt->bind_param('ii', $perPage, $offset);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public static function pastCount(): int
    {
        $db  = \App\Core\Database::connection();
        $res = $db->query("SELECT COUNT(*) c FROM events WHERE status IN ('published','completed') AND deleted_at IS NULL AND starts_at < NOW()");
        return $res ? (int) $res->fetch_assoc()['c'] : 0;
    }

    /** Public detail page: live, finished and cancelled events (drafts stay hidden). */
    public static function findPublishedBySlug(string $slug): ?array
    {
        $db   = \App\Core\Database::connection();
        $stmt = $db->prepare("SELECT * FROM events WHERE slug = ? AND status IN ('published','completed','cancelled') AND deleted_at IS NULL LIMIT 1");
        $stmt->bind_param('s', $slug);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    /** The next few upcoming events other than $excludeId. */
    public static function nextUp(int $limit = 3, int $excludeId = 0): array
    {
        $db   = \App\Core\Database::connection();
        $stmt = $db->prepare(
            "SELECT * FROM events WHERE status = 'published' AND deleted_at IS NULL AND starts_at >= NOW() AND id <> ?
              ORDER BY starts_at ASC LIMIT ?"
        );
        $stmt->bind_param('ii', $excludeId, $limit);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }
}
