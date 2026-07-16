<?php

declare(strict_types=1);

require_once __DIR__ . '/wf-uri.php';

use Capsule\Container;
use Capsule\Middleware\BasePathMiddleware;
use Capsule\Middleware\ClientAuth;
use Capsule\Middleware\DevAuth;
use Capsule\Middleware\ErrorBoundary;
use Capsule\Middleware\SecurityHeaders;
use Capsule\Middleware\StaticAssetMiddleware;
use Capsule\Router;

$container = require dirname(__DIR__) . '/config/container.php';

if (!$container instanceof Container) {
    throw new RuntimeException('config/container.php must return a Container instance.');
}

$middlewares = [
    $container->get(ErrorBoundary::class),
    $container->get(StaticAssetMiddleware::class),
    $container->get(DevAuth::class),
    $container->get(ClientAuth::class),
    $container->get(SecurityHeaders::class),
    $container->get(BasePathMiddleware::class),
];

return [$container, $container->get(Router::class), $middlewares];
