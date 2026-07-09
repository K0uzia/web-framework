<?php

declare(strict_types=1);

namespace App\Http\Dev;

use Capsule\DevDashboard;
use Capsule\ExportPathPicker;
use Capsule\Http\Factory\ResponseFactory;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\Http\Support\FormData;
use Capsule\SiteExportPath;
use Capsule\SiteExporter;
use Capsule\SiteRepository;

final class ExportController
{
    public function __construct(
        private readonly DevDashboard $ui,
        private readonly SiteRepository $site,
        private readonly SiteExporter $exporter,
        private readonly SiteExportPath $exportPath,
        private readonly ExportPathPicker $pathPicker,
        private readonly ResponseFactory $responses,
        private readonly string $projectRoot,
        private readonly string $defaultBaseUrl,
        private readonly bool $isDev,
    ) {
    }

    public function index(Request $request): Response
    {
        $site = $this->site->getSite();
        $siteName = (string) ($site['name'] ?? 'site');

        return $this->ui->render('export.html', [
            'title' => 'Créer le site',
            'crumb_html' => Breadcrumb::render([
                ['label' => 'Tableau de bord', 'href' => '/dev/overview'],
                ['label' => 'Créer le site'],
            ]),
            'site_name' => $siteName,
            'default_output_path' => $this->defaultOutputPath($siteName),
            'default_site_url' => $this->defaultBaseUrl,
            'default_base_path' => '',
            'browse_available' => $this->isDev && $this->pathPicker->isAvailable() ? '1' : '0',
            'flash' => $this->ui->flashFromRequest($request),
            'error_html' => '',
        ], section: 'export');
    }

    public function browse(Request $request): Response
    {
        if (!$this->isDev) {
            return $this->responses->json(['error' => 'Sélecteur de dossier indisponible.'], 403);
        }

        if (!$this->pathPicker->isAvailable()) {
            return $this->responses->json(['error' => 'Installez zenity ou kdialog pour parcourir les dossiers.'], 503);
        }

        $picked = $this->pathPicker->pick($this->projectRoot . '/exports');
        if ($picked === null) {
            return $this->responses->json(['cancelled' => true]);
        }

        try {
            $resolved = $this->exportPath->resolve($picked);
        } catch (\InvalidArgumentException $e) {
            return $this->responses->json(['error' => $e->getMessage()], 400);
        }

        return $this->responses->json([
            'path' => $this->displayPath($resolved),
        ]);
    }

    public function export(Request $request): Response
    {
        $data = FormData::fromRequest($request);
        $outputPath = trim((string) ($data['output_path'] ?? ''));
        $siteUrl = trim((string) ($data['site_url'] ?? ''));
        $basePath = trim((string) ($data['base_path'] ?? ''));
        $basePath = trim($basePath, '/');

        $site = $this->site->getSite();
        $siteName = (string) ($site['name'] ?? 'site');

        try {
            $resolved = $this->exportPath->resolve($outputPath);
            $result = $this->exporter->export(
                $resolved,
                $siteUrl !== '' ? $siteUrl : $this->defaultBaseUrl,
                $basePath,
            );
        } catch (\InvalidArgumentException $e) {
            return $this->renderFormError($siteName, $outputPath, $siteUrl, $basePath, $e->getMessage());
        } catch (\RuntimeException $e) {
            return $this->renderFormError($siteName, $outputPath, $siteUrl, $basePath, $e->getMessage());
        }

        $message = sprintf(
            'Site exporté dans %s (%d page%s).',
            $result->outputDir,
            $result->pageCount,
            $result->pageCount > 1 ? 's' : '',
        );

        return $this->ui->withFlash($this->ui->redirect('/dev/export'), $message);
    }

    private function renderFormError(
        string $siteName,
        string $outputPath,
        string $siteUrl,
        string $basePath,
        string $message,
    ): Response {
        return $this->ui->render('export.html', [
            'title' => 'Créer le site',
            'crumb_html' => Breadcrumb::render([
                ['label' => 'Tableau de bord', 'href' => '/dev/overview'],
                ['label' => 'Créer le site'],
            ]),
            'site_name' => $siteName,
            'default_output_path' => $outputPath !== '' ? $outputPath : $this->defaultOutputPath($siteName),
            'default_site_url' => $siteUrl !== '' ? $siteUrl : $this->defaultBaseUrl,
            'default_base_path' => $basePath,
            'browse_available' => $this->isDev && $this->pathPicker->isAvailable() ? '1' : '0',
            'flash' => '',
            'error_html' => $this->errorHtml($message),
        ], section: 'export');
    }

    private function defaultOutputPath(string $siteName): string
    {
        $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($siteName));
        $slug = trim($slug ?? 'site', '-');

        if ($slug === '') {
            $slug = 'site';
        }

        return 'exports/' . $slug;
    }

    private function displayPath(string $absolute): string
    {
        $root = realpath($this->projectRoot);
        if ($root === false) {
            return $absolute;
        }

        $root = rtrim(str_replace('\\', '/', $root), '/');
        $normalized = str_replace('\\', '/', $absolute);

        if (str_starts_with($normalized, $root . '/')) {
            return substr($normalized, strlen($root) + 1);
        }

        return $absolute;
    }

    private function errorHtml(string $message): string
    {
        if ($message === '') {
            return '';
        }

        return '<p class="dev-flash dev-flash--error" role="alert">'
            . htmlspecialchars($message, ENT_QUOTES)
            . '</p>';
    }
}
