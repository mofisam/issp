<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class ResourceDownloadLog extends Model
{
    protected static string $table = 'resource_download_logs';

    public static function record(int $resourceId, ?int $userId): void
    {
        $db = \App\Core\Database::connection();
        $stmt = $db->prepare(
            'INSERT INTO resource_download_logs (resource_id, user_id, ip_address, user_agent) VALUES (?, ?, ?, ?)'
        );
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $stmt->bind_param('iiss', $resourceId, $userId, $ip, $ua);
        $stmt->execute();
        $stmt->close();
    }

    /** How many documents this user has downloaded (every download is logged). */
    public static function countForUser(int $userId): int
    {
        $db   = \App\Core\Database::connection();
        $stmt = $db->prepare('SELECT COUNT(*) AS total FROM resource_download_logs WHERE user_id = ?');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int) ($row['total'] ?? 0);
    }
}
