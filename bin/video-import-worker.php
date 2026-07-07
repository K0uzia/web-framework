#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Worker CLI : consomme la file video_imports (queued → ready/failed).
 *
 * Usage :
 *   php bin/video-import-worker.php [--once]
 */

$root = dirname(__DIR__);

foreach (['APP_ENV', 'DB_DSN'] as $envKey) {
    $value = getenv($envKey);
    if (is_string($value) && $value !== '') {
        $_ENV[$envKey] = $value;
    }
}

require $root . '/src/Autoload.php';

/** @var array{0: Capsule\Container} */
$boot = require $root . '/bootstrap/app.php';
$container = $boot[0];

/** @var Capsule\VideoImportWorkerRunner $runner */
$runner = $container->get(Capsule\VideoImportWorkerRunner::class);

$once = in_array('--once', $argv ?? [], true);
$sleepSec = 5;

fwrite(STDOUT, "[video-import-worker] démarré" . ($once ? ' (mode --once)' : '') . PHP_EOL);

do {
    $hadJob = $runner->processNext();
    if (!$hadJob) {
        if ($once) {
            break;
        }
        sleep($sleepSec);
    } else {
        fwrite(STDOUT, "[video-import-worker] job traité" . PHP_EOL);
    }
} while (!$once);

fwrite(STDOUT, "[video-import-worker] arrêt" . PHP_EOL);
