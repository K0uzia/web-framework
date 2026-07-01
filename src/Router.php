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
     * @param list<PageRoute> $dynamicPageRoutes
     */
    public function __construct(
        private readonly array $routes,
        private readonly array $dynamicPageRoutes,
        private readonly Container $container,
    ) {
    }

    public function handle(Request $request): Response
    {
        $method = strtoupper($request->method);
        $path = $this->normalizePath($request->path);
        $key = "{$method} {$path}";

        if (isset($this->routes[$key])) {
            return $this->dispatch($this->routes[$key], $path);
        }

        foreach ($this->dynamicPageRoutes as $pageRoute) {
            if ($method !== 'GET') {
                continue;
            }

            $params = $pageRoute->match($path);
            if ($params !== null) {
                return $this->dispatchPage($pageRoute->file, $params, $path);
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
    private function dispatch(array $handler, string $path): Response
    {
        if ($handler[0] === 'page') {
            return $this->dispatchPage($handler[1], [], $path);
        }

        [$class, $method] = $handler;
        $controller = $this->container->get($class);
        if (!method_exists($controller, $method)) {
            throw new \RuntimeException("Handler not callable: {$class}::{$method}");
        }

        $response = $controller->$method();
        if (!$response instanceof Response) {
            throw new \RuntimeException("Handler must return Response: {$class}::{$method}");
        }

        return $response;
    }

    /**
     * @param array<string, string> $params
     */
    private function dispatchPage(string $file, array $params = [], string $path = '/'): Response
    {
        $renderer = $this->container->get(PageRenderer::class);

        return $renderer->render($file, $params, $path);
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

        foreach ($this->dynamicPageRoutes as $pageRoute) {
            if ($pageRoute->match($path) !== null) {
                $allowed[] = 'GET';
            }
        }

        return array_values(array_unique($allowed));
    }
}
