<?php

declare(strict_types=1);

/**
 * Diagnostic déploiement — https://lacapsule.org/wf/deploy-check.php
 * Ne passe pas par le routeur. Supprimez ce fichier une fois le déploiement validé.
 */
header('Content-Type: application/json; charset=utf-8');

$root = dirname(__DIR__);
$routerFile = $root . '/src/Router.php';
$requestFile = $root . '/src/Http/Message/Request.php';
$indexFile = $root . '/public/index.php';

$routerSrc = is_file($routerFile) ? (string) file_get_contents($routerFile) : '';

echo json_encode([
    'ok' => true,
    'deploy_marker' => 'wf-uri-v4',
    'server' => [
        'request_uri' => $_SERVER['REQUEST_URI'] ?? null,
        'script_name' => $_SERVER['SCRIPT_NAME'] ?? null,
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? null,
    ],
    'files' => [
        'project_root' => $root,
        'router_mtime' => is_file($routerFile) ? date('c', (int) filemtime($routerFile)) : null,
        'request_mtime' => is_file($requestFile) ? date('c', (int) filemtime($requestFile)) : null,
        'index_mtime' => is_file($indexFile) ? date('c', (int) filemtime($indexFile)) : null,
        'router_has_strip_wf' => str_contains($routerSrc, 'stripWfSubfolder'),
        'router_has_strip_deploy' => str_contains($routerSrc, 'stripDeployPrefix'),
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
