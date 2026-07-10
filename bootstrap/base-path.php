<?php

declare(strict_types=1);

/**
 * Détection du préfixe URL en sous-dossier (sans autoloader).
 * Utilisé avant le boot PHP complet (public/index.php, BasePath).
 */
if (!function_exists('capsule_base_path_detect')) {
    function capsule_base_path_detect(): string
    {
        $env = trim((string) (
            $_ENV['APP_BASE_PATH']
            ?? $_SERVER['APP_BASE_PATH']
            ?? getenv('APP_BASE_PATH')
            ?: ''
        ));
        if ($env !== '') {
            $normalized = '/' . trim($env, '/');

            return $normalized === '/' ? '' : $normalized;
        }

        return capsule_base_path_from_script_name();
    }

    function capsule_base_path_from_script_name(): string
    {
        $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        if ($script === '' || $script === '/index.php') {
            return '';
        }

        $dir = dirname($script);
        if ($dir === '/' || $dir === '.') {
            return '';
        }

        if (str_ends_with($dir, '/public')) {
            $parent = substr($dir, 0, -strlen('/public'));
            if ($parent !== '' && $parent !== '/') {
                return $parent;
            }

            return '';
        }

        return $dir;
    }

    function capsule_base_path_strip(string $requestPath, ?string $prefix = null): string
    {
        $prefix ??= capsule_base_path_detect();
        if ($prefix === '') {
            return $requestPath;
        }

        if ($requestPath === $prefix || $requestPath === $prefix . '/') {
            return '/';
        }

        if (str_starts_with($requestPath, $prefix . '/')) {
            $rest = substr($requestPath, strlen($prefix));

            return $rest === '' ? '/' : $rest;
        }

        return $requestPath;
    }

    function capsule_normalize_request_uri(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;

        $prefix = capsule_base_path_detect();
        if ($prefix === '') {
            return;
        }

        $normalize = static function (string $raw) use ($prefix): ?string {
            $query = '';
            if (str_contains($raw, '?')) {
                [$raw, $queryPart] = explode('?', $raw, 2);
                $query = '?' . $queryPart;
            }

            $path = rawurldecode($raw);
            if (preg_match('#^https?://[^/]+(/.*)$#i', $path, $m)) {
                $path = $m[1];
            }
            $path = preg_replace('#//+#', '/', $path) ?? $path;

            if (str_ends_with($path, '/index.php')) {
                $path = substr($path, 0, -strlen('/index.php')) ?: '/';
            }

            $prefixSlash = $prefix . '/';
            if ($path === $prefix || $path === $prefixSlash) {
                return '/' . $query;
            }
            if (str_starts_with($path, $prefixSlash)) {
                $stripped = substr($path, strlen($prefix));

                return ($stripped === '' ? '/' : $stripped) . $query;
            }

            return null;
        };

        foreach (['REQUEST_URI', 'REDIRECT_URL', 'SCRIPT_URI'] as $key) {
            $raw = (string) ($_SERVER[$key] ?? '');
            if ($raw === '') {
                continue;
            }
            $normalized = $normalize($raw);
            if ($normalized === null) {
                continue;
            }
            $_SERVER['REQUEST_URI'] = $normalized;
            $_ENV['APP_BASE_PATH'] = $prefix;
            $_SERVER['APP_BASE_PATH'] = $prefix;
            putenv('APP_BASE_PATH=' . $prefix);

            return;
        }
    }
}
