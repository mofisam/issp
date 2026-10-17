<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;

/** Call AuthMiddleware::handle() at the top of any controller method that requires a logged-in user. */
class AuthMiddleware
{
    public static function handle(): void
    {
        if (!Auth::check()) {
            header('Location: /login?redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? '/'));
            exit;
        }
    }
}
