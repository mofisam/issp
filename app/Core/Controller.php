<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    /**
     * Render a view inside a layout.
     * \$view is a dot path relative to /views, e.g. 'pages.home'.
     */
    protected function render(string $view, array $data = [], string $layout = 'layouts.main'): void
    {
        extract($data, EXTR_SKIP);
        $viewFile   = BASE_PATH . '/views/' . str_replace('.', '/', $view) . '.php';
        $layoutFile = BASE_PATH . '/views/' . str_replace('.', '/', $layout) . '.php';

        if (!is_file($viewFile)) {
            throw new \RuntimeException("View not found: {$view}");
        }

        $content = function () use ($viewFile, $data) {
            extract($data, EXTR_SKIP);
            require $viewFile;
        };

        require $layoutFile;
    }

    protected function redirect(string $path): never
    {
        header('Location: ' . $path);
        exit;
    }

    protected function json(array $payload, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    /** @return array{name:string,type:string,tmp_name:string,error:int,size:int}|null */
    protected function file(string $key): ?array
    {
        $file = $_FILES[$key] ?? null;
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        return $file;
    }

    protected function abort(int $status): never
    {
        http_response_code($status);
        require BASE_PATH . "/views/errors/{$status}.php";
        exit;
    }
}
