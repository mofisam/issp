<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class EventRegistration extends Model
{
    protected static string $table = 'event_registrations';

    public static function register(int $eventId, string $name, string $email, ?string $phone, ?string $org): bool
    {
        $db   = \App\Core\Database::connection();
        $stmt = $db->prepare(
            'INSERT INTO event_registrations (event_id, full_name, email, phone, organization_name)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('issss', $eventId, $name, $email, $phone, $org);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}
