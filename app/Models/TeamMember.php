<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class TeamMember extends Model
{
    protected static string $table = 'team_members';
    protected static bool $softDeletes = true;

    public static function published(): array
    {
        $db  = \App\Core\Database::connection();
        $sql = "SELECT * FROM team_members WHERE is_published = 1 AND deleted_at IS NULL ORDER BY `group`, sort_order";
        return $db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }
}
