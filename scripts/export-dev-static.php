<?php

declare(strict_types=1);

use App\Http\Dev\SlugCodec;
use Capsule\ChromeVariants;
use Capsule\Container;
use Capsule\Http\Message\Request;
use Capsule\Kernel;
use Capsule\Middleware\DevAuth;
use Capsule\Middleware\ErrorBoundary;
use Capsule\Middleware\SecurityHeaders;
use Capsule\PageRepository;
use Capsule\Router;
use Capsule\SiteRepository;

/**
 * Exporte les écrans GET du dashboard /dev en HTML statique (GitHub Pages).
 *
 * @return int Nombre de fichiers exportés
 */
function exportDevStatic(
    Container $container,
    string $outputDir,
    string $basePath,
    ?string $phpDevUrl = null,
    string $staticHostLabel = 'hébergement statique',
): int {
    $kernel = new Kernel(
        [
            $container->get(ErrorBoundary::class),
            $container->get(DevAuth::class),
            $container->get(SecurityHeaders::class),
        ],
        $container->get(Router::class),
    );

    $paths = collectDevExportPaths($container);
    $exported = 0;

    foreach ($paths as $path) {
        $relative = devPathToRelativeFile($path);
        $target = $outputDir . '/' . $relative;
        if (exportDevRoute($kernel, $path, $target, $basePath, $phpDevUrl, $staticHostLabel)) {
            ++$exported;
            fwrite(STDOUT, "  {$path} → {$target}\n");
        }
    }

    return $exported;
}

/**
 * @return list<string>
 */
function collectDevExportPaths(Container $container): array
{
    $paths = [
        '/dev/overview',
        '/dev/pages',
        '/dev/pages/new',
        '/dev/site',
        '/dev/theme',
        '/dev/chrome',
        '/dev/medias',
        '/dev/preview/theme',
    ];

    /** @var PageRepository $pages */
    $pages = $container->get(PageRepository::class);
    foreach ($pages->all() as $page) {
        $encoded = SlugCodec::encode($page->slug);
        $paths[] = '/dev/pages/' . $encoded;
        $paths[] = '/dev/preview/' . $encoded;
    }

    /** @var SiteRepository $site */
    $site = $container->get(SiteRepository::class);
    $siteInfo = $site->getSite();

    foreach (ChromeVariants::headerVariants($siteInfo) as $variant) {
        $id = (string) ($variant['id'] ?? '');
        if ($id !== '') {
            $paths[] = '/dev/chrome/header/' . rawurlencode($id);
        }
    }

    foreach (ChromeVariants::footerVariants($siteInfo) as $variant) {
        $id = (string) ($variant['id'] ?? '');
        if ($id !== '') {
            $paths[] = '/dev/chrome/footer/' . rawurlencode($id);
        }
    }

    return $paths;
}

function devPathToRelativeFile(string $path): string
{
    if ($path === '/dev/overview') {
        return 'dev/index.html';
    }

    $trimmed = ltrim($path, '/');

    return $trimmed . '/index.html';
}

function exportDevRoute(
    Kernel $kernel,
    string $path,
    string $targetFile,
    string $basePath,
    ?string $phpDevUrl,
    string $staticHostLabel,
): bool {
    $response = $kernel->handle(buildDevExportRequest($path));
    $status = $response->getStatus();
    if ($status >= 400) {
        fwrite(STDERR, "  skip {$path} (HTTP {$status})\n");

        return false;
    }

    $body = $response->getBody();
    if (!is_string($body) || $body === '') {
        fwrite(STDERR, "  skip {$path} (corps vide)\n");

        return false;
    }

    $html = rewriteBasePath($body, $basePath);
    $html = injectDevStaticMode($html, $basePath, $phpDevUrl, $staticHostLabel);

    $dir = dirname($targetFile);
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        fwrite(STDERR, "Impossible de créer le dossier : {$dir}\n");

        return false;
    }

    file_put_contents($targetFile, $html);

    return true;
}

function buildDevExportRequest(string $path): Request
{
    $appUrl = (string) ($_ENV['APP_URL'] ?? 'http://localhost:8080');
    $host = parse_url($appUrl, PHP_URL_HOST);
    if (!is_string($host) || $host === '') {
        $host = 'localhost';
    }

    return new Request(
        method: 'GET',
        path: $path,
        query: [],
        headers: ['Accept' => 'text/html'],
        cookies: ['capsule_dev' => '1'],
        server: [
            'HTTPS' => 'on',
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => $path,
            'HTTP_HOST' => $host,
        ],
        scheme: 'https',
        host: $host,
    );
}

function injectDevStaticMode(string $html, string $basePath, ?string $phpDevUrl, string $staticHostLabel = 'hébergement statique'): string
{
    $prefix = $basePath !== '' ? rtrim($basePath, '/') : '';
    $cssHref = $prefix . '/assets/css/dev-static.css';
    $jsHref = $prefix . '/assets/js/dev-static.js';

    $editLink = '';
    if (is_string($phpDevUrl) && $phpDevUrl !== '') {
        $safeUrl = htmlspecialchars(rtrim($phpDevUrl, '/') . '/dev/overview', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $editLink = ' <a class="dev-static-banner__link" href="' . $safeUrl . '" target="_blank" rel="noopener noreferrer">Ouvrir le panel complet (PHP)</a>';
    }

    $safeLabel = htmlspecialchars($staticHostLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $banner = '<div class="dev-static-banner" role="status">'
        . 'Mode démo statique (' . $safeLabel . '). Navigation OK, enregistrement désactivé.'
        . $editLink
        . '</div>';

    $headInjection = '    <link rel="stylesheet" href="' . htmlspecialchars($cssHref, ENT_QUOTES) . '" />' . "\n"
        . '    <script>window.__CAPSULE_DEV_STATIC=true;</script>' . "\n"
        . '    <script src="' . htmlspecialchars($jsHref, ENT_QUOTES) . '" defer></script>' . "\n";

    $html = str_replace('</head>', $headInjection . '</head>', $html);

    return str_replace('<body', $banner . "\n<body", $html);
}
