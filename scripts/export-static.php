<?php

declare(strict_types=1);

/**
 * Exporte le site public en fichiers HTML statiques (Netlify, GitHub Pages, etc.).
 *
 * Usage :
 *   APP_URL=https://example.netlify.app php scripts/export-static.php [dist]
 */

use Capsule\Page;
use Capsule\PageRenderer;
use Capsule\PageRepository;

$root = dirname(__DIR__);

foreach (['APP_ENV', 'APP_HTTPS', 'APP_URL', 'APP_BASE_PATH', 'DEV_PANEL_URL', 'NETLIFY'] as $envKey) {
    $value = getenv($envKey);
    if (is_string($value) && $value !== '') {
        $_ENV[$envKey] = $value;
    }
}

if (empty($_ENV['APP_URL'] ?? null)) {
    foreach (['DEPLOY_PRIME_URL', 'URL'] as $netlifyKey) {
        $value = getenv($netlifyKey);
        if (is_string($value) && $value !== '') {
            $_ENV['APP_URL'] = $value;
            break;
        }
    }
}

$isNetlify = ($_ENV['NETLIFY'] ?? getenv('NETLIFY')) === 'true'
    || ($_ENV['NETLIFY'] ?? getenv('NETLIFY')) === '1';

if ($isNetlify && !isset($_ENV['APP_BASE_PATH'])) {
    $_ENV['APP_BASE_PATH'] = '';
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

$phpAppUrl = getenv('PHP_APP_URL') ?: getenv('RENDER_APP_URL');
$phpAppUrl = is_string($phpAppUrl) && $phpAppUrl !== '' ? rtrim($phpAppUrl, '/') : null;

writeNetlifyFiles($outputDir, $isNetlify, $phpAppUrl);

require __DIR__ . '/export-dev-static.php';

$devPanelUrl = getenv('DEV_PANEL_URL');
$devPanelUrl = is_string($devPanelUrl) && $devPanelUrl !== '' ? $devPanelUrl : $phpAppUrl;

if ($phpAppUrl !== null) {
    fwrite(STDOUT, "Backend PHP configuré ({$phpAppUrl}) : /dev et /api seront proxifiés, pas d'export statique du dashboard.\n");
} else {
    fwrite(STDOUT, "Export dashboard /dev (mode démo statique, pas de PHP à l'exécution sur Netlify) :\n");
    $staticHostLabel = $isNetlify ? 'Netlify' : (getenv('STATIC_HOST_LABEL') ?: 'hébergement statique');
    $exported += exportDevStatic($container, $outputDir, $basePath, $devPanelUrl, $staticHostLabel);
}

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

function writeNetlifyFiles(string $outputDir, bool $isNetlify, ?string $phpAppUrl = null): void
{
    if (!$isNetlify) {
        file_put_contents($outputDir . '/.nojekyll', '');

        return;
    }

    $lines = ['# Généré par scripts/export-static.php'];

    if ($phpAppUrl !== null && $phpAppUrl !== '') {
        $origin = rtrim($phpAppUrl, '/');
        $lines[] = '/dev/*  ' . $origin . '/dev/:splat  200!';
        $lines[] = '/api/*  ' . $origin . '/api/:splat  200!';
    } else {
        $lines[] = '/dev    /dev/index.html    200';
    }

    file_put_contents($outputDir . '/_redirects', implode("\n", $lines) . "\n");
}
