<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class ContactSubmission extends Model
{
    protected static string $table = 'contact_submissions';

    public static function submit(array $data): int
    {
        return self::create([
            'first_name'    => $data['first_name'],
            'last_name'     => $data['last_name'],
            'email'         => $data['email'],
            'phone'         => $data['phone'] ?? null,
            'subject'       => $data['subject'] ?? null,
            'message'       => $data['message'],
            'audience_type' => $data['audience_type'] ?? 'general',
            'ip_address'    => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }
}
