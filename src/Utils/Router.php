<?php

namespace CNA\Utils;

use CNA\Config\Config;

class Router
{
    private array $routes = [];
    private string $basePath = '';

    public function __construct(string $basePath = '')
    {
        $this->basePath = rtrim($basePath, '/');
    }

    public function get(string $path, $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    private function addRoute(string $method, string $path, $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $this->basePath . $path,
            'handler' => $handler,
            'params' => [],
        ];
    }

    public function handleRequest(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Remove query parameters
        $path = parse_url($uri, PHP_URL_PATH);
        
        // Remove base path if set
        if ($this->basePath && strpos($path, $this->basePath) === 0) {
            $path = substr($path, strlen($this->basePath));
        }
        
        $path = $path ?: '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = $this->matchPath($route['path'], $path);
            if ($params !== false) {
                $this->callHandler($route['handler'], $params);
                return;
            }
        }

        // No route found
        $this->handleNotFound();
    }

    private function matchPath(string $routePath, string $requestPath): array|false
    {
        // Convert route path to regex pattern
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $requestPath, $matches)) {
            // Remove the full match
            array_shift($matches);
            return $matches;
        }

        return false;
    }

    private function callHandler($handler, array $params): void
    {
        if (is_string($handler)) {
            // Handle "Controller@method" syntax
            if (strpos($handler, '@') !== false) {
                [$controllerClass, $method] = explode('@', $handler, 2);
                
                if (!class_exists($controllerClass)) {
                    throw new \RuntimeException("Controller class {$controllerClass} not found");
                }

                $controller = new $controllerClass();
                if (!method_exists($controller, $method)) {
                    throw new \RuntimeException("Method {$method} not found in {$controllerClass}");
                }

                call_user_func_array([$controller, $method], $params);
            } else {
                // Simple function call
                call_user_func_array($handler, $params);
            }
        } elseif (is_callable($handler)) {
            call_user_func_array($handler, $params);
        } else {
            throw new \RuntimeException('Invalid route handler');
        }
    }

    private function handleNotFound(): void
    {
        http_response_code(404);
        $view = new View();
        $view->render('errors/404', [
            'title' => 'Page Not Found'
        ]);
    }

    public static function redirect(string $url, int $code = 302): void
    {
        header("Location: {$url}", true, $code);
        exit;
    }

    public static function url(string $path = ''): string
    {
        $baseUrl = Config::get('app.url', 'http://localhost');
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }
}