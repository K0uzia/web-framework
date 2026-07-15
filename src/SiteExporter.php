<?php

declare(strict_types=1);

namespace Capsule;

use Capsule\Http\Factory\ResponseFactory;

final class SiteExporter
{
    public function __construct(
        private readonly ResponseFactory $responseFactory,
        private readonly View $view,
        private readonly PageRepository $pages,
        private readonly SiteRepository $site,
        private readonly SectionRenderer $sections,
        private readonly SiteChrome $chrome,
        private readonly StylesheetResolver $stylesheets,
        private readonly ScriptResolver $scripts,
        private readonly string $projectRoot,
        private readonly string $publicDir,
        private readonly string $defaultBaseUrl,
    ) {
    }

    /**
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function export(string $outputDir, string $baseUrl = '', string $basePath = ''): SiteExportResult
    {
        $this->assertSafeOutputDir($outputDir);

        $baseUrl = rtrim($baseUrl !== '' ? $baseUrl : $this->defaultBaseUrl, '/');
        $basePath = rtrim($basePath, '/');

        if (is_dir($outputDir)) {
            if (!$this->isDirectoryEmpty($outputDir)) {
                throw new \InvalidArgumentException(
                    'Le dossier existe déjà et n\'est pas vide. Choisissez un autre emplacement ou videz-le manuellement.',
                );
            }
        } elseif (!mkdir($outputDir, 0755, true) && !is_dir($outputDir)) {
            throw new \RuntimeException('Impossible de créer le dossier : ' . $outputDir);
        }

        $renderer = new PageRenderer(
            $this->responseFactory,
            $this->view,
            $this->pages,
            $this->site,
            $this->sections,
            $this->chrome,
            $baseUrl,
            $this->stylesheets,
            $this->scripts,
            $this->projectRoot . '/public/assets/css',
        );

        $written = [];
        $pageCount = 0;

        foreach ($this->pages->allPublished() as $page) {
            $path = $page->routePath();
            $response = $renderer->renderBySlug($page->slug, [], $path, true);
            $body = $response->getBody();
            if (!is_string($body)) {
                throw new \RuntimeException('Corps invalide pour la page « ' . $page->slug . ' ».');
            }

            $html = $this->rewriteBasePath($body, $basePath);
            if ($basePath === '') {
                $html = $this->rewriteStaticAssetUrls($html, $this->pageAssetDepth($page));
            }
            $target = $this->pageOutputPath($outputDir, $page);
            $dir = dirname($target);
            if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
                throw new \RuntimeException('Impossible de créer le dossier : ' . $dir);
            }

            if (file_put_contents($target, $html) === false) {
                throw new \RuntimeException('Impossible d\'écrire : ' . $target);
            }

            $written[] = $target;
            ++$pageCount;
        }

        $this->copyPublicAssets($this->publicDir, $outputDir);
        $this->writeHtaccess($outputDir, $basePath);
        file_put_contents($outputDir . '/.nojekyll', '');

        return new SiteExportResult($outputDir, $pageCount, $written);
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function assertSafeOutputDir(string $outputDir): void
    {
        $root = realpath($this->projectRoot);
        if ($root === false) {
            throw new \InvalidArgumentException('Racine du projet introuvable.');
        }

        $root = rtrim(str_replace('\\', '/', $root), '/');
        $normalized = str_replace('\\', '/', $outputDir);
        $resolved = realpath($normalized);
        if ($resolved !== false) {
            $normalized = str_replace('\\', '/', $resolved);
        }

        if ($normalized === $root) {
            throw new \InvalidArgumentException('Export interdit vers la racine du projet.');
        }
    }

    private function isDirectoryEmpty(string $dir): bool
    {
        $items = scandir($dir);

        return $items !== false && array_diff($items, ['.', '..']) === [];
    }

    private function pageOutputPath(string $outputDir, Page $page): string
    {
        if ($page->slug === '') {
            return $outputDir . '/index.html';
        }

        return $outputDir . '/' . $page->slug . '/index.html';
    }

    private function rewriteBasePath(string $html, string $basePath): string
    {
        if ($basePath === '') {
            return $html;
        }

        $rewritten = preg_replace_callback(
            '#(?<attr>href|src|srcset|action)=(["\'])/(?!/)#',
            static fn (array $matches): string => $matches['attr'] . '=' . $matches[2] . $basePath . '/',
            $html,
        );

        return $rewritten ?? $html;
    }

    private function pageAssetDepth(Page $page): int
    {
        if ($page->slug === '') {
            return 0;
        }

        $parts = array_filter(explode('/', trim($page->slug, '/')), static fn (string $part): bool => $part !== '');

        return count($parts);
    }

    private function rewriteStaticAssetUrls(string $html, int $depth): string
    {
        $prefix = $depth === 0 ? '' : str_repeat('../', $depth);

        $html = preg_replace_callback(
            '#(?<attr>href|src)=(["\'])/(?!/)(?<path>(?:assets|uploads)/|favicon\.svg)#',
            static fn (array $matches): string => $matches['attr'] . '=' . $matches[2] . $prefix . $matches['path'],
            $html,
        ) ?? $html;

        $html = preg_replace_callback(
            "#url\\((['\"]?)/(assets/[^)'\"]+)(\\1)\\)#",
            static fn (array $matches): string => 'url(' . $matches[1] . $prefix . $matches[2] . $matches[3] . ')',
            $html,
        ) ?? $html;

        $html = preg_replace_callback(
            "#url\\((['\"]?)/(uploads/[^)'\"]+)(\\1)\\)#",
            static fn (array $matches): string => 'url(' . $matches[1] . $prefix . $matches[2] . $matches[3] . ')',
            $html,
        ) ?? $html;

        return $html;
    }

    /**
     * @throws \RuntimeException
     */
    private function writeHtaccess(string $outputDir, string $basePath): void
    {
        $path = $outputDir . '/.htaccess';
        if (file_put_contents($path, StaticExportHtaccess::content($basePath)) === false) {
            throw new \RuntimeException('Impossible d\'écrire : ' . $path);
        }
    }

    /**
     * @throws \RuntimeException
     */
    private function copyPublicAssets(string $publicDir, string $outputDir): void
    {
        $assetsSrc = $publicDir . '/assets';
        if (is_dir($assetsSrc)) {
            $this->copyTree($assetsSrc, $outputDir . '/assets');
        }

        $uploadsSrc = $publicDir . '/uploads';
        if (is_dir($uploadsSrc)) {
            $this->copyTree($uploadsSrc, $outputDir . '/uploads');
        }

        foreach (['favicon.svg', 'robots.txt', 'llms.txt'] as $file) {
            $src = $publicDir . '/' . $file;
            if (is_file($src)) {
                if (!copy($src, $outputDir . '/' . $file)) {
                    throw new \RuntimeException('Impossible de copier : ' . $src);
                }
            }
        }
    }

    /**
     * @throws \RuntimeException
     */
    private function copyTree(string $src, string $dest): void
    {
        if (!is_dir($dest) && !mkdir($dest, 0755, true) && !is_dir($dest)) {
            throw new \RuntimeException('Impossible de créer le dossier : ' . $dest);
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
                $this->copyTree($from, $to);
            } elseif (!copy($from, $to)) {
                throw new \RuntimeException('Impossible de copier : ' . $from);
            }
        }
    }
}
