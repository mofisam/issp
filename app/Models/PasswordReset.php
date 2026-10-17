<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

/**
 * One-time password reset tokens. Only a SHA-256 hash of the token is ever
 * stored - the raw token exists only in the emailed link.
 */
class PasswordReset extends Model
{
    protected static string $table = 'password_resets';

    private const LIFETIME_MINUTES = 60;

    /** Creates a token for the user and returns the RAW token to put in the email link. */
    public static function issue(int $userId, ?string $ip): string
    {
        $token = bin2hex(random_bytes(32));

        self::create([
            'user_id'      => $userId,
            'token_hash'   => hash('sha256', $token),
            'expires_at'   => date('Y-m-d H:i:s', time() + self::LIFETIME_MINUTES * 60),
            'requested_ip' => $ip,
        ]);

        return $token;
    }

    /** The unused, unexpired reset row for this raw token, or null. */
    public static function findValid(string $token): ?array
    {
        if ($token === '') {
            return null;
        }
        $db   = Database::connection();
        $stmt = $db->prepare(
            'SELECT * FROM `password_resets` WHERE `token_hash` = ? AND `used_at` IS NULL AND `expires_at` > NOW() LIMIT 1'
        );
        $hash = hash('sha256', $token);
        $stmt->bind_param('s', $hash);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    /** Burns every outstanding token for the user (used after a successful reset). */
    public static function consumeAllFor(int $userId): void
    {
        $db   = Database::connection();
        $stmt = $db->prepare('UPDATE `password_resets` SET `used_at` = NOW() WHERE `user_id` = ? AND `used_at` IS NULL');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->close();
    }
}
