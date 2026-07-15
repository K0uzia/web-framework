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
        private readonly ScriptResolver $scripts,
        private readonly string $publicCssDir,
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
        $data['asset_root'] = $this->basePath?->value() ?? '';
        $this->site->ensureThemeCssFile($this->publicCssDir);
        $data['theme_css'] = $this->site->themeHeadHtml($data['asset_root'], $theme, $this->publicCssDir);

        $body = $this->sections->renderAll($page->sections);
        $data = Seo::apply($data, $path, $this->baseUrl);
        $data = $this->chrome->enrich($data, $path, $publishedOnly);

        $pageSlugForCss = $slug === '' ? 'index' : $slug;
        $sectionRefs = $this->sections->extractSectionRefs($page->sections);
        $authRefs = is_array($data['login_modal_auth_refs'] ?? null) ? $data['login_modal_auth_refs'] : [];
        foreach ($authRefs as $ref) {
            if (is_array($ref)) {
                $sectionRefs[] = $ref;
            }
        }
        $bodyForAssets = $body . ($data['header_html'] ?? '') . ($data['login_modal_html'] ?? '');
        $hrefs = $this->stylesheets->resolve(
            $layout,
            $pageSlugForCss,
            $bodyForAssets,
            $data,
            $sectionRefs,
            $page->sections,
        );
        $data['stylesheets'] = $this->stylesheets->toHtml($hrefs, $data['asset_root']);

        $scriptSrcs = $this->scripts->resolve(
            $body . ($data['header_html'] ?? '') . ($data['login_modal_html'] ?? ''),
            $sectionRefs,
        );
        $data['scripts'] = $this->scripts->toHtml($scriptSrcs, $data['asset_root']);

        $html = $this->view->pageFromString($body, $data, $layout . '.html');

        return $this->responseFactory->html($html);
    }
}
