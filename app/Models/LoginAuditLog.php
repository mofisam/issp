<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class LoginAuditLog extends Model
{
    protected static string $table = 'login_audit_logs';

    public static function record(?int $userId, string $emailAttempted, string $status): void
    {
        $db   = \App\Core\Database::connection();
        $stmt = $db->prepare(
            'INSERT INTO login_audit_logs (user_id, email_attempted, status, ip_address, user_agent)
             VALUES (?, ?, ?, ?, ?)'
        );
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $stmt->bind_param('issss', $userId, $emailAttempted, $status, $ip, $ua);
        $stmt->execute();
        $stmt->close();
    }
}
