<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap/wf-uri.php';

use Capsule\Http\Emitter\SapiEmitter;
use Capsule\Http\Message\Request;
use Capsule\Kernel;
use Capsule\Middleware\BasePathMiddleware;
use Capsule\Middleware\DevAuth;
use Capsule\Middleware\ErrorBoundary;
use Capsule\Middleware\SecurityHeaders;

require dirname(__DIR__) . '/src/Autoload.php';

[$container, $router] = require dirname(__DIR__) . '/bootstrap/app.php';

/** @var array{base_path?: string} $config */
$config = $container->get('config');
$deployBase = (string) (
    $config['base_path']
    ?? $_ENV['APP_BASE_PATH']
    ?? $_SERVER['APP_BASE_PATH']
    ?? ''
);
$request = Request::fromGlobals($deployBase);

$middlewares = [
    $container->get(ErrorBoundary::class),
    $container->get(DevAuth::class),
    $container->get(SecurityHeaders::class),
    $container->get(BasePathMiddleware::class),
];

$kernel = new Kernel($middlewares, $router);
$response = $kernel->handle($request);

(new SapiEmitter())->emit($response);
