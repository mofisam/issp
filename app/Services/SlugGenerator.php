<?php
declare(strict_types=1);

namespace App\Services;

class SlugGenerator
{
    /**
     * Builds a unique slug by appending -2, -3, ... if needed.
     * \$exists is a callback: fn(string \$slug): bool
     */
    public static function unique(string $title, callable $exists): string
    {
        $base = slugify($title);
        $slug = $base;
        $i = 2;
        while ($exists($slug)) {
            $slug = $base . '-' . $i;
            $i++;
        }
        return $slug;
    }
}
