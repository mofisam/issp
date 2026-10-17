<?php
declare(strict_types=1);

namespace App\Middleware;

/**
 * Very small session-based rate limiter - fine for a login form on a single
 * app server. For real production traffic, rate-limit at the webserver or
 * with a shared store (Redis/memcached) instead of \$_SESSION.
 */
class RateLimitMiddleware
{
    public static function tooManyAttempts(string $key, int $maxAttempts = 5, int $decaySeconds = 300): bool
    {
        $bucket = $_SESSION['_rate_limit'][$key] ?? ['count' => 0, 'expires' => time() + $decaySeconds];

        if (time() > $bucket['expires']) {
            $bucket = ['count' => 0, 'expires' => time() + $decaySeconds];
        }

        $bucket['count']++;
        $_SESSION['_rate_limit'][$key] = $bucket;

        return $bucket['count'] > $maxAttempts;
    }
}
