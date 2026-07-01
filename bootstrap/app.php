<?php

declare(strict_types=1);

use Capsule\Container;
use Capsule\Router;

$container = require dirname(__DIR__) . '/config/container.php';

if (!$container instanceof Container) {
    throw new RuntimeException('config/container.php must return a Container instance.');
}

return [$container, $container->get(Router::class)];
