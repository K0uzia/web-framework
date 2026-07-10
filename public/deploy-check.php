<?php

declare(strict_types=1);

/**
 * Diagnostic déploiement — /deploy-check.php (dev uniquement).
 */
$appEnv = strtolower(trim((string) (getenv('APP_ENV') ?: $_SERVER['APP_ENV'] ?? 'prod')));
if ($appEnv !== 'dev') {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Not Found';

    exit;
}

header('Content-Type: application/json; charset=utf-8');

$root = dirname(__DIR__);
$routerFile = $root . '/src/Router.php';
$requestFile = $root . '/src/Http/Message/Request.php';
$indexFile = $root . '/public/index.php';

$routerSrc = is_file($routerFile) ? (string) file_get_contents($routerFile) : '';

$basePath = (static function (): string {
    require_once dirname(__DIR__) . '/bootstrap/base-path.php';

    return capsule_base_path_detect();
})();

$sampleCss = 'assets/css/base.css';
$sampleCssDisk = $root . '/public/' . $sampleCss;
$sampleCssUrl = ($basePath !== '' ? $basePath : '') . '/' . $sampleCss;

echo json_encode([
    'ok' => true,
    'deploy_marker' => 'base-path-v2',
    'detected_base_path' => $basePath !== '' ? $basePath : null,
    'server' => [
        'request_uri' => $_SERVER['REQUEST_URI'] ?? null,
        'script_name' => $_SERVER['SCRIPT_NAME'] ?? null,
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? null,
    ],
    'assets' => [
        'sample_css_disk' => $sampleCssDisk,
        'sample_css_exists' => is_file($sampleCssDisk),
        'sample_css_url' => $sampleCssUrl,
        'hint' => is_file($sampleCssDisk)
            ? 'Ouvrez sample_css_url dans le navigateur : 200 = OK, 404 = .htaccess ou document root incorrect.'
            : 'Le fichier CSS est absent sur le disque : vérifiez que public/assets/ a bien été uploadé.',
    ],
    'files' => [
        'project_root' => $root,
        'router_mtime' => is_file($routerFile) ? date('c', (int) filemtime($routerFile)) : null,
        'request_mtime' => is_file($requestFile) ? date('c', (int) filemtime($requestFile)) : null,
        'index_mtime' => is_file($indexFile) ? date('c', (int) filemtime($indexFile)) : null,
        'router_has_strip_detected' => str_contains($routerSrc, 'normalizePath'),
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
