<?php

declare(strict_types=1);

namespace App\View;

use Capsule\BasePath;
use Capsule\ClientDashboardConfig;
use Capsule\Http\Factory\ResponseFactory;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\Http\Support\Cookie;
use Capsule\SiteRepository;
use Capsule\View;

final class AdminDashboard
{
    public function __construct(
        private readonly string $adminRoot,
        private readonly ResponseFactory $responses,
        private readonly SiteRepository $site,
        private readonly ?BasePath $basePath = null,
        private readonly string $publicCssDir = '',
    ) {
    }

    private function view(): View
    {
        return new View(
            $this->adminRoot . '/layouts',
            $this->adminRoot . '/partials',
            $this->adminRoot . '/pages',
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(string $template, array $data = [], int $status = 200): Response
    {
        $data = $this->withSiteChrome($data);
        $html = $this->view()->page($template, $data, 'admin.html');

        if ($this->basePath !== null && !$this->basePath->isEmpty()) {
            $html = $this->basePath->rewriteHtml($html);
        }

        return $this->responses->html($html, $status);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function renderAuth(string $template, array $data = [], int $status = 200): Response
    {
        $data = $this->withSiteChrome($data);
        $html = $this->view()->page($template, $data, 'auth.html');

        if ($this->basePath !== null && !$this->basePath->isEmpty()) {
            $html = $this->basePath->rewriteHtml($html);
        }

        return $this->responses->html($html, $status);
    }

    public function redirect(string $location): Response
    {
        return $this->responses->redirect($location);
    }

    public function withFlash(Response $response, string $message): Response
    {
        $path = $this->basePath !== null ? $this->basePath->cookiePath('/admin') : '/admin';

        return $this->responses->withCookie($response, Cookie::create('capsule_flash', $message, [
            'path' => $path,
            'httpOnly' => false,
            'maxAge' => 60,
        ]));
    }

    public function flashFromRequest(Request $request): string
    {
        $flash = $request->cookies['capsule_flash'] ?? '';

        return is_string($flash) ? $flash : '';
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function withSiteChrome(array $data): array
    {
        $site = $this->site->getSite();
        $name = is_string($site['name'] ?? null) && $site['name'] !== ''
            ? $site['name']
            : 'Mon site';
        $logoUrl = is_string($site['logo_url'] ?? null) ? trim($site['logo_url']) : '';
        $faviconUrl = is_string($site['favicon_url'] ?? null) ? trim($site['favicon_url']) : '';
        $assetRoot = $this->basePath?->value() ?? '';

        $data['site_name'] = $name;
        $data['site_name_escaped'] = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $data['logo_url'] = $logoUrl;
        $data['logo_html'] = $this->logoHtml($name, $logoUrl);
        $data['favicon_href'] = $faviconUrl !== ''
            ? $faviconUrl
            : $assetRoot . '/assets/favicon-dev.png';
        $data['asset_root'] = $assetRoot;
        $data['base_path'] = $assetRoot;
        $data['base_path_json'] = json_encode($assetRoot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $data['theme_css'] = $this->site->themeHeadHtml($assetRoot, null, $this->publicCssDir);
        $data['flash'] ??= '';

        $nav = is_string($data['nav_section'] ?? null) ? $data['nav_section'] : 'pages';
        foreach (['pages', 'medias'] as $key) {
            $active = $nav === $key;
            $data['nav_active_' . $key] = $active ? 'is-active' : '';
            $data['nav_aria_' . $key] = $active ? ' aria-current="page"' : '';
        }

        $mediasEnabled = ClientDashboardConfig::isMediasEnabled($this->site->getClientDashboard());
        $data['nav_medias_hidden'] = $mediasEnabled ? '' : 'hidden';

        return $data;
    }

    private function logoHtml(string $name, string $logoUrl): string
    {
        if ($logoUrl === '') {
            $initial = mb_strtoupper(mb_substr($name, 0, 1));

            return '<span class="admin-brand__mark admin-brand__mark--text" aria-hidden="true">'
                . htmlspecialchars($initial, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                . '</span>';
        }

        $src = htmlspecialchars($logoUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<img class="admin-brand__logo" src="' . $src . '" alt="" width="40" height="40" />';
    }
}
