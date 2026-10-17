<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class NewsletterSubscriber extends Model
{
    protected static string $table = 'newsletter_subscribers';
    protected static bool $softDeletes = true;

    public static function subscribe(string $email, ?string $name, string $source): array
    {
        $existing = self::findBy('email', $email);
        if ($existing) {
            return ['created' => false, 'id' => $existing['id']];
        }
        $id = self::create([
            'email'     => $email,
            'full_name' => $name,
            'source'    => $source,
            'status'    => 'pending',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
        return ['created' => true, 'id' => $id];
    }
}
