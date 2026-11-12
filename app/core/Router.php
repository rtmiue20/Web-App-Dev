<?php

/**
 * Router - Bộ định tuyến trung tâm theo kiến trúc Front Controller & MVC.
 * Hỗ trợ:
 * - Đa phương thức HTTP: GET, POST, PUT, DELETE, PATCH, ANY & Method Spoofing (_method).
 * - Tham số động trên URL (ví dụ: /posts/{id}, /admin/categories/edit/{id}).
 * - Nhóm route theo tiền tố & Middleware (group('/admin', [ModeratorMiddleware::class], ...)).
 * - Khởi tạo Controller lười (Lazy Instantiation) qua [ControllerClass::class, 'method'] hoặc 'Controller@method'.
 */
class Router
{
    private array $routes = [];
    private string $groupPrefix = '';
    private array $groupMiddleware = [];

    public function get(string $path, callable|array|string $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, callable|array|string $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    public function put(string $path, callable|array|string $handler, array $middleware = []): void
    {
        $this->add('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, callable|array|string $handler, array $middleware = []): void
    {
        $this->add('DELETE', $path, $handler, $middleware);
    }

    public function patch(string $path, callable|array|string $handler, array $middleware = []): void
    {
        $this->add('PATCH', $path, $handler, $middleware);
    }

    public function any(string $path, callable|array|string $handler, array $middleware = []): void
    {
        foreach (['GET', 'POST', 'PUT', 'DELETE', 'PATCH'] as $method) {
            $this->add($method, $path, $handler, $middleware);
        }
    }

    /**
     * Gom nhóm các route dùng chung tiền tố URL và Middleware bảo vệ (RBAC / Auth).
     */
    public function group(string $prefix, array $middleware, callable $callback): void
    {
        $previousPrefix = $this->groupPrefix;
        $previousMiddleware = $this->groupMiddleware;

        $this->groupPrefix = rtrim($previousPrefix, '/') . '/' . trim($prefix, '/');
        $this->groupMiddleware = array_merge($previousMiddleware, $middleware);

        $callback($this);

        $this->groupPrefix = $previousPrefix;
        $this->groupMiddleware = $previousMiddleware;
    }

    private function add(string $method, string $path, callable|array|string $handler, array $middleware): void
    {
        $fullPath = $this->groupPrefix !== ''
            ? rtrim($this->groupPrefix, '/') . '/' . ltrim($path, '/')
            : $path;

        $mergedMiddleware = array_values(array_unique(array_merge($this->groupMiddleware, $middleware)));

        $this->routes[strtoupper($method)][self::normalizePath($fullPath)] = [
            'handler'    => $handler,
            'middleware' => $mergedMiddleware,
        ];
    }

    /**
     * Điều phối request từ Front Controller tới Middleware & Controller đích.
     */
    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);

        // Hỗ trợ Method Spoofing (_method = PUT | DELETE | PATCH) trong form HTML POST
        if ($method === 'POST' && !empty($_POST['_method'])) {
            $spoofed = strtoupper(trim((string)$_POST['_method']));
            if (in_array($spoofed, ['PUT', 'DELETE', 'PATCH'], true)) {
                $method = $spoofed;
            }
        }

        $path = self::normalizePath((string)parse_url($uri, PHP_URL_PATH));

        $matchedRoute = null;
        $params = [];

        // 1. Khớp chính xác đường dẫn tĩnh
        if (isset($this->routes[$method][$path])) {
            $matchedRoute = $this->routes[$method][$path];
        } else {
            // 2. Khớp đường dẫn có tham số động (ví dụ: /posts/{id})
            foreach ($this->routes[$method] ?? [] as $routePath => $route) {
                if (str_contains($routePath, '{')) {
                    $pattern = '#^' . preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $routePath) . '$#';
                    if (preg_match($pattern, $path, $matches)) {
                        $matchedRoute = $route;
                        foreach ($matches as $key => $val) {
                            if (is_string($key)) {
                                $params[$key] = ctype_digit((string)$val) ? (int)$val : $val;
                            }
                        }
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

        // 3. Thực thi chuỗi Middleware bảo mật (AuthMiddleware, ModeratorMiddleware, AdminMiddleware)
        foreach ($matchedRoute['middleware'] as $middleware) {
            if (!$middleware::handle()) {
                return;
            }
        }

        // 4. Giải quyết Handler và gọi Action trên Controller
        $callable = $this->resolveHandler($matchedRoute['handler']);
        call_user_func_array($callable, array_values($params));
    }

    /**
     * Hỗ trợ handler dạng ['ControllerClass', 'method'], 'ControllerClass@method', [$instance, 'method'] hoặc Closure.
     */
    private function resolveHandler(callable|array|string $handler): callable
    {
        if (is_string($handler) && str_contains($handler, '@')) {
            [$class, $method] = explode('@', $handler, 2);
            $handler = [$class, $method];
        }

        if (is_array($handler) && count($handler) === 2 && is_string($handler[0])) {
            $class = $handler[0];
            $method = $handler[1];
            $instance = new $class();
            return [$instance, $method];
        }

        if (is_callable($handler)) {
            return $handler;
        }

        throw new RuntimeException('Route handler không hợp lệ.');
    }

    private static function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '//' ? '/' : $path;
    }
}
