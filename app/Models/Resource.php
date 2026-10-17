<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Resource extends Model
{
    protected static string $table = 'resources';
    protected static bool $softDeletes = true;

    /** Resources any visitor (not logged in) may download. */
    public static function publicOnly(): array
    {
        return self::whereAll('visibility', RESOURCE_VISIBILITY_PUBLIC, 'created_at DESC');
    }

    /**
     * Resources visible to a given logged-in user: public + stakeholder-tier
     * resources the user's organization/role is granted access to.
     * See resource_access_grants in the schema - an empty grant list for a
     * resource means "any authenticated stakeholder".
     */
    public static function visibleTo(array $user): array
    {
        $db   = \App\Core\Database::connection();
        $stmt = $db->prepare(
            "SELECT DISTINCT r.* FROM resources r
             LEFT JOIN resource_access_grants g ON g.resource_id = r.id
             LEFT JOIN user_roles ur ON ur.user_id = ?
             WHERE r.deleted_at IS NULL
               AND (
                    r.visibility = 'public'
                    OR (
                        r.visibility = 'stakeholder'
                        AND (
                            NOT EXISTS (SELECT 1 FROM resource_access_grants g2 WHERE g2.resource_id = r.id)
                            OR g.organization_id = ?
                            OR g.role_id = ur.role_id
                        )
                    )
               )
             ORDER BY r.created_at DESC"
        );
        $orgId = $user['organization_id'] ?? 0;
        $stmt->bind_param('ii', $user['id'], $orgId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public static function userCanAccess(array $resource, ?array $user): bool
    {
        if ($resource['visibility'] === RESOURCE_VISIBILITY_PUBLIC) {
            return true;
        }
        if (!$user) {
            return false;
        }
        if ($resource['visibility'] === RESOURCE_VISIBILITY_PRIVATE) {
            return \App\Core\Auth::isStaff();
        }
        // stakeholder tier: check grants
        $visible = self::visibleTo($user);
        return in_array($resource['id'], array_column($visible, 'id'));
    }

    /** Current grants for a resource: ['organizations' => int[], 'roles' => int[]]. */
    public static function grantsFor(int $resourceId): array
    {
        $out  = ['organizations' => [], 'roles' => []];
        $db   = \App\Core\Database::connection();
        $stmt = $db->prepare('SELECT organization_id, role_id FROM resource_access_grants WHERE resource_id = ?');
        $stmt->bind_param('i', $resourceId);
        $stmt->execute();
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $row) {
            if ($row['organization_id'] !== null) {
                $out['organizations'][] = (int) $row['organization_id'];
            }
            if ($row['role_id'] !== null) {
                $out['roles'][] = (int) $row['role_id'];
            }
        }
        $stmt->close();
        return $out;
    }

    /** Replaces all grants for the resource. Empty lists = any signed-in stakeholder. */
    public static function syncGrants(int $resourceId, array $organizationIds, array $roleIds): void
    {
        $db = \App\Core\Database::connection();
        $del = $db->prepare('DELETE FROM resource_access_grants WHERE resource_id = ?');
        $del->bind_param('i', $resourceId);
        $del->execute();
        $del->close();

        $ins = $db->prepare('INSERT INTO resource_access_grants (resource_id, organization_id, role_id) VALUES (?, ?, ?)');
        foreach (array_unique(array_filter(array_map('intval', $organizationIds))) as $orgId) {
            $null = null;
            $ins->bind_param('iii', $resourceId, $orgId, $null);
            $ins->execute();
        }
        foreach (array_unique(array_filter(array_map('intval', $roleIds))) as $roleId) {
            $null = null;
            $ins->bind_param('iii', $resourceId, $null, $roleId);
            $ins->execute();
        }
        $ins->close();
    }

    public static function incrementDownloadCount(int $id): void
    {
        $db = \App\Core\Database::connection();
        $stmt = $db->prepare('UPDATE resources SET download_count = download_count + 1 WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    }
}
