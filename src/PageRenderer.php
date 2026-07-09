<?php

declare(strict_types=1);

namespace Capsule;

use Capsule\Http\Exception\HttpException;
use Capsule\Http\Factory\ResponseFactory;
use Capsule\Http\Message\Response;

final class PageRenderer
{
    public function __construct(
        private readonly ResponseFactory $responseFactory,
        private readonly View $view,
        private readonly PageRepository $pages,
        private readonly SiteRepository $site,
        private readonly SectionRenderer $sections,
        private readonly SiteChrome $chrome,
        private readonly string $baseUrl,
        private readonly StylesheetResolver $stylesheets,
        private readonly bool $publishedOnly = true,
        private readonly ?BasePath $basePath = null,
    ) {
    }

    /**
     * @param array<string, string> $params
     */
    public function renderBySlug(string $slug, array $params = [], string $path = '/', ?bool $publishedOnly = null): Response
    {
        $publishedOnly ??= $this->publishedOnly;
        $page = $this->pages->findBySlug($slug, $publishedOnly);
        if ($page === null) {
            throw new HttpException(404, 'Not Found');
        }

        $data = array_merge([
            'title' => $page->title,
            'description' => $page->description,
            'layout' => $page->layout,
        ], $page->meta, $params);

        $layout = $page->layout;
        unset($data['layout']);

        $theme = $this->site->getTheme();
        $data['theme'] = $theme;
        $data['theme_css'] = '<style>' . $this->site->themeCss() . '</style>';

        $body = $this->sections->renderAll($page->sections);
        $data = Seo::apply($data, $path, $this->baseUrl);
        $data = $this->chrome->enrich($data, $path, $publishedOnly);
        $data['asset_root'] = $this->basePath?->value() ?? '';

        $pageSlugForCss = $slug === '' ? 'index' : $slug;
        $hrefs = $this->stylesheets->resolve(
            $layout,
            $pageSlugForCss,
            $body,
            $data,
            $this->sections->extractSectionRefs($page->sections),
        );
        $data['stylesheets'] = $this->stylesheets->toHtml($hrefs);

        $html = $this->view->pageFromString($body, $data, $layout . '.html');

        if ($this->basePath !== null && !$this->basePath->isEmpty()) {
            $html = $this->basePath->rewriteHtml($html);
        }

        return $this->responseFactory->html($html);
    }
}
