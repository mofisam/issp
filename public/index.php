<?php
/**
 * Front controller - every request is routed through this file.
 * (public/.htaccess rewrites all non-file requests here.)
 */

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

// --- Minimal PSR-4-style autoloader for the App\ namespace -----------------
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = BASE_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

// Composer autoload (PHPMailer etc.) if installed
if (is_file(BASE_PATH . '/vendor/autoload.php')) {
    require BASE_PATH . '/vendor/autoload.php';
}

require BASE_PATH . '/app/Config/config.php';
require BASE_PATH . '/app/Config/constants.php';
require BASE_PATH . '/app/Core/Helpers.php';

use App\Core\Router;
use App\Core\Session;

Session::start();

$router = new Router();
require BASE_PATH . '/routes.php';

$uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$router->dispatch($method, rtrim($uri, '/') ?: '/');