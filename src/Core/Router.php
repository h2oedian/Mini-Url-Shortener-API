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
                //run middleware
                foreach ($route['middleware'] as $middlewareClass) {
                    $middleware = new $middlewareClass();
                    if (!$middleware->handle($request)) {
                        return;
                    }
                }

                // run handler
                $handler = $route['handler'];

                // support [Controller::class, 'method']
                if (is_array($handler) && count($handler) === 2) {
                    [$controllerClass, $method] = $handler;
                    $controller = new $controllerClass();

                    array_shift($matches); // delete full match
                    array_unshift($matches, $request, $response); // add Request and Response

                    call_user_func_array([$controller, $method], $matches);
                }
                // support string 'Controller@method'
                elseif (is_string($handler) && strpos($handler, '@') !== false) {
                    [$controllerName, $method] = explode('@', $handler);
                    $controllerClass = "App\\Controllers\\{$controllerName}";
                    $controller = new $controllerClass();

                    array_shift($matches); // delete full match
                    array_unshift($matches, $request, $response); //add Request and Response

                    call_user_func_array([$controller, $method], $matches);
                }
                // support callable
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
