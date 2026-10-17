<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Core\Database;

/** Writes to audit_logs - who changed what, when (see database schema). */
class AuditLogger
{
    public static function log(string $action, string $entityType, int $entityId, array $changes = []): void
    {
        $user = Auth::user();
        $db   = Database::connection();
        $stmt = $db->prepare(
            'INSERT INTO audit_logs (user_id, action, entity_type, entity_id, changes_json, ip_address)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $userId    = $user['id'] ?? null;
        $changes   = json_encode($changes);
        $ip        = $_SERVER['REMOTE_ADDR'] ?? null;
        $stmt->bind_param('ississ', $userId, $action, $entityType, $entityId, $changes, $ip);
        // Note: bind_param type string above is illustrative; adjust types to match
        // actual column order if you extend this method.
        $stmt->execute();
        $stmt->close();
    }
}
