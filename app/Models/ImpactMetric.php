<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class ImpactMetric extends Model
{
    protected static string $table = 'impact_metrics';

    public static function published(): array
    {
        $db  = \App\Core\Database::connection();
        $sql = 'SELECT * FROM impact_metrics WHERE is_published = 1 ORDER BY sort_order';
        return $db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }
}
