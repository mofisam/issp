<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;

/** Restricts a controller method to logged-in stakeholder-portal users. */
class StakeholderMiddleware
{
    public static function handle(): void
    {
        AuthMiddleware::handle();

        if (!Auth::isStakeholder()) {
            http_response_code(403);
            require BASE_PATH . '/views/errors/403.php';
            exit;
        }
    }
}
