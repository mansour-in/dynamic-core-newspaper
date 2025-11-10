<?php

declare(strict_types=1);

namespace CoreNewspaper\Core;

use Closure;
use RuntimeException;

final class Router
{
    /**
     * @var array<string, array<int, array{pattern:string, handler:callable}>>
     */
    private array $routes = [];

    public function add(string $method, string $pattern, callable $handler): void
    {
        $method = strtoupper($method);
        $this->routes[$method][] = [
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    public function get(string $pattern, callable $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    public function dispatch(Request $request): mixed
    {
        $method = $request->method();
        $path = $request->uri();
        $routes = $this->routes[$method] ?? [];
        foreach ($routes as $route) {
            $pattern = $this->convertPattern($route['pattern']);
            if (preg_match($pattern, $path, $matches) === 1) {
                array_shift($matches);
                return call_user_func($route['handler'], $request, ...$matches);
            }
        }

        throw new RuntimeException('Route not found');
    }

    private function convertPattern(string $pattern): string
    {
        if ($pattern === '/') {
            return '#^/$#';
        }

        $pattern = preg_replace('#\{([a-zA-Z0-9_]+)\}#', '(?P<$1>[^/]+)', $pattern);
        return '#^' . rtrim($pattern, '/') . '$#';
    }
}
