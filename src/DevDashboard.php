<?php

declare(strict_types=1);

namespace Capsule;

use Capsule\Http\Factory\ResponseFactory;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\Http\Support\Cookie;

final class DevDashboard
{
    public function __construct(
        private readonly string $devRoot,
        private readonly ResponseFactory $responses,
        private readonly ?SiteRepository $site = null,
        private readonly ?BasePath $basePath = null,
    ) {
    }

    private function view(): View
    {
        return new View(
            $this->devRoot . '/layouts',
            $this->devRoot . '/partials',
            $this->devRoot . '/pages',
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(string $template, array $data = [], int $status = 200, string $section = ''): Response
    {
        if ($section === '') {
            $section = match (true) {
                str_starts_with($template, 'chrome') => 'chrome',
                str_starts_with($template, 'site') => 'site',
                str_starts_with($template, 'theme') => 'theme',
                str_starts_with($template, 'medias') => 'medias',
                str_starts_with($template, 'video-imports') => 'video_imports',
                str_starts_with($template, 'export') => 'export',
                str_starts_with($template, 'overview') => 'overview',
                default => 'pages',
            };
        }
        $data = self::withNav($data, $section);
        $data['crumb_html'] ??= '';
        $data['theme_css'] = $this->themeCssMarkup();
        $data['base_path'] = $this->basePath?->value() ?? '';
        $data['base_path_json'] = json_encode($data['base_path'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $html = $this->view()->page($template, $data, 'dev.html');

        return $this->responses->html($html, $status);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public static function withNav(array $data, string $section): array
    {
        foreach (['overview', 'pages', 'medias', 'site', 'chrome', 'theme', 'export'] as $key) {
            $active = $section === $key;
            $data['nav_active_' . $key] = $active ? 'is-active' : '';
            $data['nav_aria_' . $key] = $active ? ' aria-current="page"' : '';
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function renderAuth(string $template, array $data = [], int $status = 200): Response
    {
        $data['base_path'] = $this->basePath?->value() ?? '';
        $data['base_path_json'] = json_encode($data['base_path'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $html = $this->view()->page($template, $data, 'auth.html');

        return $this->responses->html($html, $status);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function partial(string $template, array $data = []): Response
    {
        return $this->responses->html($this->view()->render($template, $data));
    }

    /**
     * @param array<string, mixed> $data
     */
    public function partialHtml(string $template, array $data = []): string
    {
        return $this->view()->render($template, $data);
    }

    public function fragment(string $html, int $status = 200): Response
    {
        return $this->responses->html($html, $status);
    }

    public function redirect(string $location): Response
    {
        return $this->responses->redirect($location);
    }

    public function withFlash(Response $response, string $message): Response
    {
        $path = $this->basePath !== null ? $this->basePath->cookiePath('/dev') : '/dev';

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

    private function themeCssMarkup(): string
    {
        if ($this->site === null) {
            return '';
        }

        return '<style>' . $this->site->themeCss() . '</style>';
    }
}
