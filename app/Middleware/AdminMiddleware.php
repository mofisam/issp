<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;

/** Restricts a controller method to CMS staff (any of the staff roles). */
class AdminMiddleware
{
    public static function handle(): void
    {
        AuthMiddleware::handle();

        if (!Auth::isStaff()) {
            http_response_code(403);
            require BASE_PATH . '/views/errors/403.php';
            exit;
        }
    }

    public static function requireRole(string ...$roles): void
    {
        self::handle();
        if (!Auth::hasRole(...$roles)) {
            http_response_code(403);
            require BASE_PATH . '/views/errors/403.php';
            exit;
        }
    }
}
