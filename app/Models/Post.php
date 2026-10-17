<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Post extends Model
{
    protected static string $table = 'posts';
    protected static bool $softDeletes = true;

    public static function publishedPaginated(int $page = 1, int $perPage = 9, ?int $categoryId = null): array
    {
        $db     = \App\Core\Database::connection();
        $offset = ($page - 1) * $perPage;
        $cat    = $categoryId ?? 0;
        $stmt   = $db->prepare(
            "SELECT * FROM posts WHERE status = 'published' AND deleted_at IS NULL AND (? = 0 OR category_id = ?)
              ORDER BY published_at DESC, id DESC LIMIT ? OFFSET ?"
        );
        $stmt->bind_param('iiii', $cat, $cat, $perPage, $offset);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public static function publishedCount(?int $categoryId = null): int
    {
        $db   = \App\Core\Database::connection();
        $cat  = $categoryId ?? 0;
        $stmt = $db->prepare("SELECT COUNT(*) c FROM posts WHERE status = 'published' AND deleted_at IS NULL AND (? = 0 OR category_id = ?)");
        $stmt->bind_param('ii', $cat, $cat);
        $stmt->execute();
        $n = (int) ($stmt->get_result()->fetch_assoc()['c'] ?? 0);
        $stmt->close();
        return $n;
    }

    /** Categories that have at least one published post: id, name, slug, total. */
    public static function categoriesInUse(): array
    {
        $db  = \App\Core\Database::connection();
        $res = $db->query(
            "SELECT c.id, c.name, c.slug, COUNT(p.id) AS total
               FROM post_categories c
               JOIN posts p ON p.category_id = c.id AND p.status = 'published' AND p.deleted_at IS NULL
              GROUP BY c.id, c.name, c.slug ORDER BY c.name ASC"
        );
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    /** Most recent published posts, optionally skipping one (for "keep reading"). */
    public static function latest(int $limit = 3, int $excludeId = 0): array
    {
        $db   = \App\Core\Database::connection();
        $stmt = $db->prepare(
            "SELECT * FROM posts WHERE status = 'published' AND deleted_at IS NULL AND id <> ?
              ORDER BY published_at DESC, id DESC LIMIT ?"
        );
        $stmt->bind_param('ii', $excludeId, $limit);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public static function findPublishedBySlug(string $slug): ?array
    {
        $db   = \App\Core\Database::connection();
        $stmt = $db->prepare("SELECT * FROM posts WHERE slug = ? AND status = 'published' AND deleted_at IS NULL LIMIT 1");
        $stmt->bind_param('s', $slug);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }
}
