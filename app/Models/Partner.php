<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Partner extends Model
{
    protected static string $table = 'partners';
    protected static bool $softDeletes = true;

    public static function published(): array
    {
        $db  = \App\Core\Database::connection();
        $sql = "SELECT * FROM partners WHERE is_published = 1 AND deleted_at IS NULL ORDER BY sort_order";
        return $db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }
}
