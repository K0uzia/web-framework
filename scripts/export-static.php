<?php

declare(strict_types=1);

/**
 * Exporte le site public en fichiers HTML statiques (GitHub Pages, hébergement statique).
 *
 * Usage :
 *   APP_URL=https://user.github.io/repo APP_BASE_PATH=/repo php scripts/export-static.php [dist]
 */

use Capsule\Page;
use Capsule\PageRenderer;
use Capsule\PageRepository;

$root = dirname(__DIR__);

foreach (['APP_ENV', 'APP_HTTPS', 'APP_URL', 'APP_BASE_PATH'] as $envKey) {
    $value = getenv($envKey);
    if (is_string($value) && $value !== '') {
        $_ENV[$envKey] = $value;
    }
}

$_ENV['APP_ENV'] ??= 'prod';
$_ENV['APP_HTTPS'] ??= '1';

require $root . '/src/Autoload.php';

/** @var array{0: \Capsule\Container} */
$boot = require $root . '/bootstrap/app.php';
$container = $boot[0];

$outputDir = isset($argv[1]) && $argv[1] !== '' ? $argv[1] : $root . '/dist';
$basePath = rtrim((string) ($_ENV['APP_BASE_PATH'] ?? getenv('APP_BASE_PATH') ?: ''), '/');

/** @var PageRenderer $renderer */
$renderer = $container->get(PageRenderer::class);
/** @var PageRepository $pages */
$pages = $container->get(PageRepository::class);

if (is_dir($outputDir)) {
    removeTree($outputDir);
}
mkdir($outputDir, 0755, true);

$exported = 0;
foreach ($pages->allPublished() as $page) {
    $path = $page->routePath();
    $response = $renderer->renderBySlug($page->slug, [], $path, true);
    $body = $response->getBody();
    if (!is_string($body)) {
        fwrite(STDERR, "Corps invalide pour la page « {$page->slug} »\n");
        exit(1);
    }

    $html = rewriteBasePath($body, $basePath);
    $target = pageOutputPath($outputDir, $page);
    $dir = dirname($target);
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        fwrite(STDERR, "Impossible de créer le dossier : {$dir}\n");
        exit(1);
    }
    file_put_contents($target, $html);
    ++$exported;
    fwrite(STDOUT, "  {$path} → {$target}\n");
}

copyPublicAssets($root . '/public', $outputDir);
file_put_contents($outputDir . '/.nojekyll', '');

require __DIR__ . '/export-dev-static.php';

$phpDevUrl = getenv('DEV_PANEL_URL');
$phpDevUrl = is_string($phpDevUrl) && $phpDevUrl !== '' ? $phpDevUrl : null;

fwrite(STDOUT, "Export dashboard /dev :\n");
$devExported = exportDevStatic($container, $outputDir, $basePath, $phpDevUrl);
$exported += $devExported;

fwrite(STDOUT, "Export terminé : {$exported} fichier(s) dans {$outputDir}\n");

/**
 * @return never
 */
function removeTree(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }
    $items = scandir($dir);
    if ($items === false) {
        return;
    }
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            removeTree($path);
        } else {
            unlink($path);
        }
    }
    rmdir($dir);
}

function pageOutputPath(string $outputDir, Page $page): string
{
    if ($page->slug === '') {
        return $outputDir . '/index.html';
    }

    return $outputDir . '/' . $page->slug . '/index.html';
}

function rewriteBasePath(string $html, string $basePath): string
{
    if ($basePath === '') {
        return $html;
    }

    $rewritten = preg_replace_callback(
        '#(?<attr>href|src|action)=(["\'])/(?!/)#',
        static fn (array $matches): string => $matches['attr'] . '=' . $matches[2] . $basePath . '/',
        $html,
    );

    return $rewritten ?? $html;
}

function copyPublicAssets(string $publicDir, string $outputDir): void
{
    $assetsSrc = $publicDir . '/assets';
    if (is_dir($assetsSrc)) {
        copyTree($assetsSrc, $outputDir . '/assets');
    }

    $uploadsSrc = $publicDir . '/uploads';
    if (is_dir($uploadsSrc)) {
        copyTree($uploadsSrc, $outputDir . '/uploads');
    }

    foreach (['favicon.svg', 'robots.txt', 'llms.txt'] as $file) {
        $src = $publicDir . '/' . $file;
        if (is_file($src)) {
            copy($src, $outputDir . '/' . $file);
        }
    }
}

function copyTree(string $src, string $dest): void
{
    if (!is_dir($dest) && !mkdir($dest, 0755, true) && !is_dir($dest)) {
        fwrite(STDERR, "Impossible de créer le dossier : {$dest}\n");
        exit(1);
    }

    $items = scandir($src);
    if ($items === false) {
        return;
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $from = $src . '/' . $item;
        $to = $dest . '/' . $item;
        if (is_dir($from)) {
            copyTree($from, $to);
        } else {
            copy($from, $to);
        }
    }
}
