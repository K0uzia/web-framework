<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap/base-path.php';
capsule_normalize_request_uri();

use Capsule\Http\Emitter\SapiEmitter;
use Capsule\Http\Message\Request;
use Capsule\Kernel;

require dirname(__DIR__) . '/src/Autoload.php';

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
