<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class ProgrammePillar extends Model
{
    protected static string $table = 'programme_pillars';

    public static function published(): array
    {
        $db  = \App\Core\Database::connection();
        $sql = "SELECT * FROM programme_pillars WHERE is_published = 1 AND deleted_at IS NULL ORDER BY sort_order";
        return $db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }
}
