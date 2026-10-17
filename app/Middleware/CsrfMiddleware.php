<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Csrf;

/** Call at the top of any POST-handling controller method. */
class CsrfMiddleware
{
    public static function handle(): void
    {
        $token = $_POST['_csrf'] ?? null;
        if (!Csrf::verify($token)) {
            http_response_code(419);
            echo 'Your session has expired. Please go back and try again.';
            exit;
        }
    }
}
