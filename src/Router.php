<?php

declare(strict_types=1);

namespace Capsule;

use Capsule\Http\Exception\HttpException;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\Middleware\HandlerInterface;

final class Router implements HandlerInterface
{
    /**
     * @param array<string, array{0: class-string, 1: string}|array{0: 'page', 1: string}> $routes
     * @param list<PageRoute>                                                             $dynamicPageRoutes
     * @param list<array{methods: list<string>, pattern: string, handler: array{0: class-string, 1: string}}> $patternRoutes
     */
    public function __construct(
        private readonly array $routes,
        private readonly array $dynamicPageRoutes,
        private readonly Container $container,
        private readonly array $patternRoutes = [],
    ) {
    }

    public function handle(Request $request): Response
    {
        $method = strtoupper($request->method);
        $path = $this->normalizePath($request->path);
        $key = "{$method} {$path}";

        if (isset($this->routes[$key])) {
            return $this->dispatch($this->routes[$key], $request, $path);
        }

        foreach ($this->patternRoutes as $route) {
            if (!in_array($method, $route['methods'], true)) {
                continue;
            }
            if (!preg_match($route['pattern'], $path, $matches)) {
                continue;
            }

            $params = [];
            foreach ($matches as $name => $value) {
                if (is_string($name)) {
                    $params[$name] = $value;
                }
            }

            return $this->dispatchController($route['handler'], $request, $params);
        }

        foreach ($this->dynamicPageRoutes as $pageRoute) {
            if ($method !== 'GET') {
                continue;
            }

            $params = $pageRoute->match($path);
            if ($params !== null) {
                return $this->dispatchPage($pageRoute->slug, $params, $path);
            }
        }

        $allowed = $this->allowedMethodsForPath($path);
        if ($allowed !== []) {
            throw new HttpException(405, 'Method Not Allowed', ['Allow' => [implode(', ', $allowed)]]);
        }

        throw new HttpException(404, 'Not Found');
    }

    /**
     * @param array{0: class-string, 1: string}|array{0: 'page', 1: string} $handler
     */
    private function dispatch(array $handler, Request $request, string $path): Response
    {
        if ($handler[0] === 'page') {
            return $this->dispatchPage($handler[1], [], $path);
        }

        return $this->dispatchController($handler, $request);
    }

    /**
     * @param array{0: class-string, 1: string}     $handler
     * @param array<string, string>                  $params
     */
    private function dispatchController(array $handler, Request $request, array $params = []): Response
    {
        [$class, $method] = $handler;
        $controller = $this->container->get($class);
        if (!method_exists($controller, $method)) {
            throw new \RuntimeException("Handler not callable: {$class}::{$method}");
        }

        $response = $controller->$method($request, ...array_values($params));
        if (!$response instanceof Response) {
            throw new \RuntimeException("Handler must return Response: {$class}::{$method}");
        }

        return $response;
    }

    /**
     * @param array<string, string> $params
     */
    private function dispatchPage(string $slug, array $params = [], string $path = '/'): Response
    {
        $renderer = $this->container->get(PageRenderer::class);

        return $renderer->renderBySlug($slug, $params, $path);
    }

    private function normalizePath(string $path): string
    {
        if ($path === '' || $path === '/') {
            return '/';
        }

        return '/' . trim($path, '/');
    }

    /**
     * @return list<string>
     */
    private function allowedMethodsForPath(string $path): array
    {
        $allowed = [];
        foreach ($this->routes as $routeKey => $_) {
            [$method, $routePath] = explode(' ', $routeKey, 2);
            if ($routePath === $path) {
                $allowed[] = $method;
            }
        }

        foreach ($this->patternRoutes as $route) {
            if (preg_match($route['pattern'], $path)) {
                array_push($allowed, ...$route['methods']);
            }
        }

        foreach ($this->dynamicPageRoutes as $pageRoute) {
            if ($pageRoute->match($path) !== null) {
                $allowed[] = 'GET';
            }
        }

        return array_values(array_unique($allowed));
    }

    /**
     * @param array<string, array{0: class-string, 1: string}> $routes
     *
     * @return array{
     *   exact: array<string, array{0: class-string, 1: string}>,
     *   patterns: list<array{methods: list<string>, pattern: string, handler: array{0: class-string, 1: string}}>
     * }
     */
    public static function splitRoutes(array $routes): array
    {
        $exact = [];
        $patterns = [];

        foreach ($routes as $key => $handler) {
            if (!preg_match('/^(GET|POST|PUT|PATCH|DELETE|HEAD|OPTIONS)\s+(.+)$/', $key, $m)) {
                $exact[$key] = $handler;
                continue;
            }

            $method = $m[1];
            $path = $m[2];

            if (!str_contains($path, '{')) {
                $exact[$key] = $handler;
                continue;
            }

            $regex = preg_replace_callback(
                '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
                static fn (array $match): string => '(?<' . $match[1] . '>[^/]+)',
                $path,
            ) ?? $path;

            $patterns[] = [
                'methods' => [$method],
                'pattern' => '#^' . $regex . '$#',
                'handler' => $handler,
            ];
        }

        return ['exact' => $exact, 'patterns' => $patterns];
    }
}
