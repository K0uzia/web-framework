<?php

declare(strict_types=1);

// Serveur de dev PHP (`php -S`) : les fichiers statiques (css/js/images) sous
// public/ doivent être servis tels quels, sans passer par le routeur applicatif.
// Sur Apache/nginx en production, le serveur web les sert avant même d'atteindre
// ce script ; ce garde-fou ne s'active donc que pour le SAPI cli-server.
if (PHP_SAPI === 'cli-server') {
    $assetPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if (is_string($assetPath) && $assetPath !== '' && $assetPath !== '/') {
        $assetFile = __DIR__ . rawurldecode($assetPath);
        if (is_file($assetFile)) {
            return false;
        }
    }
}

require dirname(__DIR__) . '/bootstrap/base-path.php';
capsule_normalize_request_uri();

use Capsule\Http\Emitter\SapiEmitter;
use Capsule\Http\Message\Request;
use Capsule\Kernel;

require dirname(__DIR__) . '/src/Autoload.php';

// PHP built-in server: /favicon.ico n'existe pas (Apache le mappe via .htaccess).
$faviconPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if (is_string($faviconPath) && str_ends_with($faviconPath, '/favicon.ico')) {
    $svg = __DIR__ . '/favicon.svg';
    if (is_file($svg)) {
        header('Content-Type: image/svg+xml; charset=utf-8');
        header('Cache-Control: public, max-age=86400');
        readfile($svg);
        exit;
    }
}

[$container, $router, $middlewares] = require dirname(__DIR__) . '/bootstrap/app.php';

/** @var array{base_path?: string} $config */
$config = $container->get('config');
$deployBase = (string) (
    $config['base_path']
    ?? $_ENV['APP_BASE_PATH']
    ?? $_SERVER['APP_BASE_PATH']
    ?? ''
);
$request = Request::fromGlobals($deployBase);

$kernel = new Kernel($middlewares, $router);
$response = $kernel->handle($request);

(new SapiEmitter())->emit($response);
