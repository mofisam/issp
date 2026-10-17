<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected static string $table = 'users';
    protected static bool $softDeletes = true;

    public static function withRoles(int $id): ?array
    {
        $user = self::find($id);
        if (!$user) {
            return null;
        }
        $user['roles'] = \App\Core\Auth::roles();
        return $user;
    }

    /** @return string[] role names currently assigned to this user */
    public static function roleNamesFor(int $userId): array
    {
        $db   = \App\Core\Database::connection();
        $stmt = $db->prepare(
            'SELECT r.name FROM roles r
             INNER JOIN user_roles ur ON ur.role_id = r.id
             WHERE ur.user_id = ?'
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return array_column($rows, 'name');
    }

    /** @return int[] ids of users who hold the given role name - used to filter the user list by role, since roles live in a pivot table rather than a column on users. */
    public static function idsWithRole(string $roleName): array
    {
        $db   = \App\Core\Database::connection();
        $stmt = $db->prepare(
            'SELECT ur.user_id FROM user_roles ur
             INNER JOIN roles r ON r.id = ur.role_id
             WHERE r.name = ?'
        );
        $stmt->bind_param('s', $roleName);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return array_map('intval', array_column($rows, 'user_id'));
    }

    /** @return int[] role IDs currently assigned to this user */
    public static function roleIdsFor(int $userId): array
    {
        $db   = \App\Core\Database::connection();
        $stmt = $db->prepare('SELECT role_id FROM user_roles WHERE user_id = ?');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return array_map('intval', array_column($rows, 'role_id'));
    }

    /** Replaces a user's role assignments with the given list of role IDs. */
    public static function syncRoles(int $userId, array $roleIds): void
    {
        $db = \App\Core\Database::connection();

        $stmt = $db->prepare('DELETE FROM user_roles WHERE user_id = ?');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->close();

        if (!$roleIds) {
            return;
        }

        $stmt = $db->prepare('INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)');
        foreach ($roleIds as $roleId) {
            $roleId = (int) $roleId;
            $stmt->bind_param('ii', $userId, $roleId);
            $stmt->execute();
        }
        $stmt->close();
    }
}
