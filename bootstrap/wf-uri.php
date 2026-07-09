<?php

declare(strict_types=1);

/**
 * Normalisation URI production lacapsule.org/wf (sans autoloader).
 * Inclus en premier depuis public/index.php.
 */
(function (): void {
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    $prefix = '/wf';

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
})();
