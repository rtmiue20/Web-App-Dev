<?php

class Router
{
    private array $routes = [];

    public function get(string $path, callable $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, callable $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    private function add(string $method, string $path, callable $handler, array $middleware): void
    {
        $this->routes[$method][self::normalizePath($path)] = [
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = self::normalizePath((string) parse_url($uri, PHP_URL_PATH));
        $route = $this->routes[strtoupper($method)][$path] ?? null;

        if ($route === null) {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            return;
        }

        foreach ($route['middleware'] as $middleware) {
            if (!$middleware::handle()) {
                return;
            }
        }

        ($route['handler'])();
    }

    private static function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '//' ? '/' : $path;
    }
}