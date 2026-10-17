<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Faq extends Model
{
    protected static string $table = 'faqs';

    public static function published(): array
    {
        $db  = \App\Core\Database::connection();
        $sql = 'SELECT * FROM faqs WHERE is_published = 1 ORDER BY sort_order';
        return $db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }
}
