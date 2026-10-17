<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\User;

/**
 * Session-based auth shared by the CMS admin and the stakeholder portal.
 * A user's roles come from user_roles -> roles (see schema), so one table
 * serves both audiences - we just check role names where it matters.
 */
class Auth
{
    private static ?array $cachedUser = null;

    public static function attempt(string $email, string $password): bool
    {
        $user = User::findBy('email', $email);

        if (!$user || $user['status'] !== 'active') {
            return false;
        }

        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            return false;
        }

        if (!password_verify($password, $user['password_hash'])) {
            User::update($user['id'], [
                'failed_login_attempts' => (int) $user['failed_login_attempts'] + 1,
            ]);
            return false;
        }

        Session::regenerate();
        Session::put('user_id', $user['id']);
        User::update($user['id'], [
            'failed_login_attempts' => 0,
            'last_login_at'         => date('Y-m-d H:i:s'),
            'last_login_ip'         => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);

        self::$cachedUser = null;
        return true;
    }

    public static function logout(): void
    {
        Session::destroy();
        self::$cachedUser = null;
    }

    public static function check(): bool
    {
        return Session::get('user_id') !== null;
    }

    public static function user(): ?array
    {
        if (self::$cachedUser !== null) {
            return self::$cachedUser;
        }
        $id = Session::get('user_id');
        if (!$id) {
            return null;
        }
        self::$cachedUser = User::find($id);
        return self::$cachedUser;
    }

    /** @return string[] role names for the current user */
    public static function roles(): array
    {
        $user = self::user();
        if (!$user) {
            return [];
        }
        $db   = Database::connection();
        $stmt = $db->prepare(
            'SELECT r.name FROM roles r
             INNER JOIN user_roles ur ON ur.role_id = r.id
             WHERE ur.user_id = ?'
        );
        $stmt->bind_param('i', $user['id']);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return array_column($rows, 'name');
    }

    public static function hasRole(string ...$roleNames): bool
    {
        return count(array_intersect($roleNames, self::roles())) > 0;
    }

    public static function isStaff(): bool
    {
        $user = self::user();
        return $user !== null && $user['user_type'] === USER_TYPE_STAFF;
    }

    public static function isStakeholder(): bool
    {
        $user = self::user();
        return $user !== null && $user['user_type'] === USER_TYPE_STAKEHOLDER;
    }
}
