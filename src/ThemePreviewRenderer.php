<?php

declare(strict_types=1);

namespace Capsule;

use Capsule\Http\Factory\ResponseFactory;
use Capsule\Http\Message\Response;

/**
 * Aperçu live du thème : échantillons UI, pas le contenu du site public.
 */
final class ThemePreviewRenderer
{
    /** @var list<array{type: string, variant: string}> */
    private const SECTION_REFS = [
        ['type' => 'hero', 'variant' => 'centered'],
        ['type' => 'ui-alert', 'variant' => 'success'],
        ['type' => 'ui-card', 'variant' => 'simple'],
    ];

    public function __construct(
        private readonly ResponseFactory $responses,
        private readonly View $view,
        private readonly SiteRepository $site,
        private readonly SiteChrome $chrome,
        private readonly StylesheetResolver $stylesheets,
        private readonly ScriptResolver $scripts,
        private readonly string $publicCssDir,
        private readonly string $baseUrl,
    ) {
    }

    public function render(): Response
    {
        $path = '/dev/preview/theme';

        $data = [
            'title' => 'Aperçu du thème',
            'description' => 'Échantillons pour tester couleurs, typographie et composants du site.',
            'layout' => 'default',
        ];
        $data['theme'] = $this->site->getTheme();
        $this->site->ensureThemeCssFile($this->publicCssDir);
        $data['asset_root'] = '';
        $data['theme_css'] = $this->site->themeHeadHtml('', $data['theme'], $this->publicCssDir);
        $data = Seo::apply($data, $path, $this->baseUrl);
        $data = $this->chrome->enrich($data, $path, false);

        $body = $this->view->render('theme-preview.html', []);

        $hrefs = $this->stylesheets->resolve(
            'default',
            'theme-preview',
            $body,
            $data,
            array_merge(
                self::SECTION_REFS,
                is_array($data['login_modal_auth_refs'] ?? null) ? $data['login_modal_auth_refs'] : [],
            ),
        );
        $hrefs[] = '/assets/css/theme-preview.css';
        $data['stylesheets'] = $this->stylesheets->toHtml($hrefs);

        $scriptSrcs = $this->scripts->resolve(
            $body . ($data['header_html'] ?? '') . ($data['login_modal_html'] ?? ''),
            array_merge(
                self::SECTION_REFS,
                is_array($data['login_modal_auth_refs'] ?? null) ? $data['login_modal_auth_refs'] : [],
            ),
        );
        $data['scripts'] = $this->scripts->toHtml($scriptSrcs);

        $html = $this->view->pageFromString($body, $data, 'default.html');
        $html = str_replace(
            '</head>',
            '<meta name="robots" content="noindex, nofollow" /></head>',
            $html,
        );

        return $this->responses->html($html);
    }
}
