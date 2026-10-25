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
        $this->routes[strtoupper($method)][self::normalizePath($path)] = [
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);
        $path = self::normalizePath((string) parse_url($uri, PHP_URL_PATH));

        $matchedRoute = null;
        $params = [];

        // Exact match
        if (isset($this->routes[$method][$path])) {
            $matchedRoute = $this->routes[$method][$path];
        } else {
            // Parameterized route matching (e.g. /posts/{id})
            foreach ($this->routes[$method] ?? [] as $routePath => $route) {
                if (str_contains($routePath, '{')) {
                    $pattern = '#^' . preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $routePath) . '$#';
                    if (preg_match($pattern, $path, $matches)) {
                        $matchedRoute = $route;
                        $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                        break;
                    }
                }
            }
        }

        if ($matchedRoute === null) {
            http_response_code(404);
            $errorFile = __DIR__ . '/../views/errors/404.php';
            if (file_exists($errorFile)) {
                require $errorFile;
            } else {
                echo '404 - Không tìm thấy trang';
            }
            return;
        }

        foreach ($matchedRoute['middleware'] as $middleware) {
            if (!$middleware::handle()) {
                return;
            }
        }

        call_user_func_array($matchedRoute['handler'], array_values($params));
    }

    private static function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '//' ? '/' : $path;
    }
}