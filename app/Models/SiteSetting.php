<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class SiteSetting extends Model
{
    protected static string $table = 'site_settings';
    protected static bool $softDeletes = true;

    /** @var array<string,?string>|null per-request cache of every setting */
    private static ?array $cache = null;

    /** Every setting as key => value (one query per request; empty on DB failure). */
    public static function map(): array
    {
        if (self::$cache === null) {
            self::$cache = [];
            try {
                foreach (self::all('id ASC') as $row) {
                    self::$cache[$row['setting_key']] = $row['setting_value'];
                }
            } catch (\Throwable $e) {
                // Never let a settings problem take the public site down.
            }
        }
        return self::$cache;
    }

    /**
     * Value for $key. $default is used only when the setting has never been
     * saved; a value an admin saved as blank stays blank (so a field can be hidden).
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        $map = self::map();
        if (!array_key_exists($key, $map) || $map[$key] === null) {
            return $default;
        }
        return trim($map[$key]);
    }

    public static function setValue(string $key, ?string $value, string $group, ?int $updatedBy): void
    {
        self::$cache = null;
        $existing = self::findBy('setting_key', $key);
        if ($existing) {
            self::update($existing['id'], [
                'setting_value' => $value,
                'setting_group' => $group,
                'updated_by'    => $updatedBy,
            ]);
            return;
        }
        self::create([
            'setting_key'   => $key,
            'setting_value' => $value,
            'setting_group' => $group,
            'updated_by'    => $updatedBy,
        ]);
    }
}
