<?php

declare(strict_types=1);

use Capsule\Http\Emitter\SapiEmitter;
use Capsule\Http\Message\Request;
use Capsule\Kernel;
use Capsule\Middleware\ErrorBoundary;
use Capsule\Middleware\SecurityHeaders;

require dirname(__DIR__) . '/src/Autoload.php';

[$container, $router] = require dirname(__DIR__) . '/bootstrap/app.php';

$request = Request::fromGlobals();

$middlewares = [
    $container->get(ErrorBoundary::class),
    $container->get(SecurityHeaders::class),
];

$kernel = new Kernel($middlewares, $router);
$response = $kernel->handle($request);

(new SapiEmitter())->emit($response);
