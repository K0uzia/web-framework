<?php

declare(strict_types=1);

namespace App\Http\Dev;

use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\PageRenderer;

final class PreviewController
{
    public function __construct(private readonly PageRenderer $pages)
    {
    }

    public function show(Request $request, string $slug): Response
    {
        $decoded = SlugCodec::decode($slug);
        $path = $decoded === '' ? '/' : '/' . $decoded;

        // Permet de prévisualiser une variante d'en-tête / pied de page qui
        // n'est pas encore active (éditeur En-tête & pied de page).
        $params = [];
        $headerVariant = $request->query['header_variant'] ?? '';
        $footerVariant = $request->query['footer_variant'] ?? '';
        if (is_string($headerVariant) && $headerVariant !== '') {
            $params['preview_header_variant'] = $headerVariant;
        }
        if (is_string($footerVariant) && $footerVariant !== '') {
            $params['preview_footer_variant'] = $footerVariant;
        }

        $response = $this->pages->renderBySlug($decoded, $params, $path, false);

        $chromeOnly = $request->query['chrome_only'] ?? '';
        if ($chromeOnly === 'header' || $chromeOnly === 'footer') {
            $response = $this->isolateChrome($response, $chromeOnly);
        }

        return $response;
    }

    /**
     * Masque le reste de la page pour ne laisser visible que l'en-tête ou le
     * pied de page (aperçu de l'éditeur En-tête & pied de page).
     */
    private function isolateChrome(Response $response, string $part): Response
    {
        $html = $response->getBody();
        if (!is_string($html)) {
            return $response;
        }

        $hidden = $part === 'header' ? '.site-main, .site-footer' : '.site-main, .site-header';
        $style = '<style>' . $hidden . ' { display: none !important; }</style>';

        return $response->withBody(str_replace('</head>', $style . '</head>', $html));
    }
}
