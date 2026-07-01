<?php

declare(strict_types=1);

namespace Capsule;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class PageScanner
{
    /**
     * @return array{
     *   static: array<string, PageRoute>,
     *   dynamic: list<PageRoute>
     * }
     */
    public function discover(string $pagesDir): array
    {
        $pagesDir = rtrim($pagesDir, '/');
        if (!is_dir($pagesDir)) {
            return ['static' => [], 'dynamic' => []];
        }

        $static = [];
        $dynamic = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($pagesDir, \FilesystemIterator::SKIP_DOTS)
        );

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'html') {
                continue;
            }

            $absolute = $file->getPathname();
            $real = realpath($absolute);
            $pagesReal = realpath($pagesDir);
            if ($real === false || $pagesReal === false) {
                continue;
            }
            if (!str_starts_with($real, $pagesReal . DIRECTORY_SEPARATOR) && $real !== $pagesReal) {
                continue;
            }

            $relative = ltrim(str_replace($pagesDir, '', $real), '/');
            $route = $this->fileToRoute($relative, $real);
            if ($route === null) {
                continue;
            }

            if ($route->paramNames === []) {
                $path = $this->routePathFromFile($relative);
                $static["GET {$path}"] = $route;
            } else {
                $dynamic[] = $route;
            }
        }

        return ['static' => $static, 'dynamic' => $dynamic];
    }

    private function fileToRoute(string $relative, string $absolute): ?PageRoute
    {
        $relative = str_replace('\\', '/', $relative);
        if (str_contains($relative, '..')) {
            return null;
        }

        $path = $this->routePathFromFile($relative);
        if (preg_match_all('/\[([a-zA-Z_][a-zA-Z0-9_]*)\]/', $path, $matches)) {
            $paramNames = $matches[1];
            $patternPath = preg_replace('/\[([a-zA-Z_][a-zA-Z0-9_]*)\]/', '(?<$1>[^/]+)', $path) ?? $path;
            $pattern = '#^' . $patternPath . '$#';

            return new PageRoute($absolute, $pattern, $paramNames);
        }

        return new PageRoute($absolute, '#^' . preg_quote($path, '#') . '$#');
    }

    private function routePathFromFile(string $relative): string
    {
        $relative = preg_replace('/\.html$/', '', $relative) ?? $relative;
        $relative = preg_replace('/\/index$/', '', $relative) ?? $relative;
        if ($relative === 'index') {
            $relative = '';
        }

        $segments = array_values(array_filter(explode('/', $relative), static fn (string $s) => $s !== ''));
        if ($segments === []) {
            return '/';
        }

        return '/' . implode('/', $segments);
    }
}
