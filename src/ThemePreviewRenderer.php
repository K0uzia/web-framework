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
        $data['theme_css'] = '<style>' . $this->site->themeCss() . '</style>';
        $data = Seo::apply($data, $path, $this->baseUrl);
        $data = $this->chrome->enrich($data, $path, false);

        $body = $this->view->render('theme-preview.html', []);

        $hrefs = $this->stylesheets->resolve('default', 'theme-preview', $body, $data, self::SECTION_REFS);
        $hrefs[] = '/assets/css/theme-preview.css';
        $data['stylesheets'] = $this->stylesheets->toHtml($hrefs);

        $html = $this->view->pageFromString($body, $data, 'default.html');
        $html = str_replace(
            '</head>',
            '<meta name="robots" content="noindex, nofollow" /></head>',
            $html,
        );

        return $this->responses->html($html);
    }
}
