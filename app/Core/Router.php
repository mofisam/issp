<?php
declare(strict_types=1);

namespace App\Core;

class Router
{
    /** @var array<int, array{0:string,1:string,2:array}> */
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->routes[] = ['GET', $path, $handler];
    }

    public function post(string $path, array $handler): void
    {
        $this->routes[] = ['POST', $path, $handler];
    }

    public function dispatch(string $method, string $uri): void
    {
        foreach ($this->routes as [$routeMethod, $path, $handler]) {
            if ($routeMethod !== $method) {
                continue;
            }

            $pattern = preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $path);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                $params = array_filter(
                    $matches,
                    fn ($key) => is_string($key),
                    ARRAY_FILTER_USE_KEY
                );

                [$class, $methodName] = $handler;
                $controller = new $class();
                $controller->{$methodName}(...array_values($params));
                return;
            }
        }

        http_response_code(404);
        require BASE_PATH . '/views/errors/404.php';
    }
}
