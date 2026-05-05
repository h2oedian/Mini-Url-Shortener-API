<?php

namespace App\Core;

class Router
{
    private $routes = [];

    public function get(string $path, $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function delete(string $path, $handler, array $middleware = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    private function addRoute(string $method, string $path, $handler, array $middleware = []): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(Request $request, Response $response): void
    {
        $requestMethod = $request->getMethod();
        $requestPath = $request->getPath();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            $pattern = $this->convertPathToRegex($route['path']);

            if (preg_match($pattern, $requestPath, $matches)) {
                // اجرای middlewareها
                foreach ($route['middleware'] as $middlewareClass) {
                    $middleware = new $middlewareClass();
                    if (!$middleware->handle($request)) {
                        return;
                    }
                }

                // اجرای handler
                $handler = $route['handler'];

                // پشتیبانی از فرمت آرایه‌ای [Controller::class, 'method']
                if (is_array($handler) && count($handler) === 2) {
                    [$controllerClass, $method] = $handler;
                    $controller = new $controllerClass();

                    array_shift($matches); // حذف full match
                    array_unshift($matches, $request, $response); // اضافه کردن Request و Response

                    call_user_func_array([$controller, $method], $matches);
                }
                // پشتیبانی از فرمت string 'Controller@method'
                elseif (is_string($handler) && strpos($handler, '@') !== false) {
                    [$controllerName, $method] = explode('@', $handler);
                    $controllerClass = "App\\Controllers\\{$controllerName}";
                    $controller = new $controllerClass();

                    array_shift($matches); // حذف full match
                    array_unshift($matches, $request, $response); // اضافه کردن Request و Response

                    call_user_func_array([$controller, $method], $matches);
                }
                // پشتیبانی از callable عادی
                elseif (is_callable($handler)) {
                    $handler($request, $response);
                }

                return;
            }
        }

        $response->json(['error' => 'Route not found'], 404);
    }

    private function convertPathToRegex(string $path): string
    {
        $regex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_-]+)', $path);
        return '#^' . $regex . '$#';
    }
}
