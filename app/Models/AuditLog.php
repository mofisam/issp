<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class AuditLog extends Model
{
    protected static string $table = 'audit_logs';

    /**
     * The most recent activity across the CMS, with the acting user's name
     * joined in for display. Used by the admin dashboard's activity feed.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function recent(int $limit = 8): array
    {
        $db   = \App\Core\Database::connection();
        $stmt = $db->prepare(
            'SELECT a.action, a.entity_type, a.entity_id, a.created_at,
                    u.first_name, u.last_name
             FROM audit_logs a
             LEFT JOIN users u ON u.id = a.user_id
             ORDER BY a.created_at DESC
             LIMIT ?'
        );
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    /**
     * Daily activity counts for the trailing N days (including today), with
     * gap days filled in at zero so the dashboard's trend chart always has
     * a full, evenly-spaced series to draw.
     *
     * @return array<int, array{date: string, total: int}>
     */
    public static function countsByDay(int $days = 7): array
    {
        $db   = \App\Core\Database::connection();
        $stmt = $db->prepare(
            'SELECT DATE(created_at) AS day, COUNT(*) AS total
             FROM audit_logs
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
             GROUP BY DATE(created_at)'
        );
        $stmt->bind_param('i', $days);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $byDay = [];
        foreach ($rows as $row) {
            $byDay[$row['day']] = (int) $row['total'];
        }

        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $series[] = ['date' => $date, 'total' => $byDay[$date] ?? 0];
        }
        return $series;
    }

    /**
     * Paged, optionally filtered activity feed for the full activity log
     * page - unlike recent(), this joins in enough of the actor's details
     * for a standalone page rather than a dashboard widget.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function paginated(int $page = 1, int $perPage = 25, ?string $action = null, ?string $entityType = null): array
    {
        [$where, $types, $params] = self::filterClause($action, $entityType);

        $offset = max(0, ($page - 1) * $perPage);
        $sql = "SELECT a.action, a.entity_type, a.entity_id, a.ip_address, a.created_at,
                       u.first_name, u.last_name, u.email
                FROM audit_logs a
                LEFT JOIN users u ON u.id = a.user_id
                {$where}
                ORDER BY a.created_at DESC
                LIMIT ? OFFSET ?";

        $db   = \App\Core\Database::connection();
        $stmt = $db->prepare($sql);
        $types   .= 'ii';
        $params[] = $perPage;
        $params[] = $offset;
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    /** Total matching rows for paginated(), with the same filters. */
    public static function countAll(?string $action = null, ?string $entityType = null): int
    {
        [$where, $types, $params] = self::filterClause($action, $entityType);
        $db  = \App\Core\Database::connection();
        $sql = "SELECT COUNT(*) AS total FROM audit_logs a {$where}";

        if ($types === '') {
            $row = $db->query($sql)->fetch_assoc();
            return (int) ($row['total'] ?? 0);
        }

        $stmt = $db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int) ($row['total'] ?? 0);
    }

    /** Distinct action values seen so far, for the activity log's filter dropdown. */
    public static function distinctActions(): array
    {
        $db = \App\Core\Database::connection();
        return array_column($db->query('SELECT DISTINCT action FROM audit_logs ORDER BY action')->fetch_all(MYSQLI_ASSOC), 'action');
    }

    /** Distinct entity_type values seen so far, for the activity log's filter dropdown. */
    public static function distinctEntityTypes(): array
    {
        $db = \App\Core\Database::connection();
        return array_column($db->query('SELECT DISTINCT entity_type FROM audit_logs ORDER BY entity_type')->fetch_all(MYSQLI_ASSOC), 'entity_type');
    }

    /** @return array{0: string, 1: string, 2: array<int, string>} [whereSql, bindTypes, bindParams] */
    private static function filterClause(?string $action, ?string $entityType): array
    {
        $conditions = [];
        $types      = '';
        $params     = [];

        if ($action !== null && $action !== '') {
            $conditions[] = 'a.action = ?';
            $types       .= 's';
            $params[]     = $action;
        }
        if ($entityType !== null && $entityType !== '') {
            $conditions[] = 'a.entity_type = ?';
            $types       .= 's';
            $params[]     = $entityType;
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        return [$where, $types, $params];
    }
}
