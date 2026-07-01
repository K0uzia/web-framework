<?php

declare(strict_types=1);

namespace Capsule;

use Capsule\Http\Factory\ResponseFactory;
use Capsule\Http\Message\Response;

final class PageRenderer
{
    public function __construct(
        private readonly ResponseFactory $responseFactory,
        private readonly View $view,
        private readonly string $layoutsDir,
        private readonly string $baseUrl,
        private readonly StylesheetResolver $stylesheets,
    ) {
    }

    /**
     * @param array<string, string> $params
     */
    public function render(string $templateFile, array $params = [], string $path = '/'): Response
    {
        $raw = file_get_contents($templateFile);
        if ($raw === false) {
            throw new \RuntimeException("Cannot read page template: {$templateFile}");
        }

        $parsed = Frontmatter::parse($raw);
        $data = $parsed['meta'];

        $dataFile = YamlData::siblingDataFile($templateFile);
        if ($dataFile !== null) {
            $data = array_merge($data, YamlData::loadFile($dataFile));
        }

        $data = array_merge($data, $params);

        $layout = is_scalar($data['layout'] ?? null) ? (string) $data['layout'] : 'default';
        unset($data['layout']);

        $data = Seo::apply($data, $path, $this->baseUrl);

        $pageSlug = pathinfo($templateFile, PATHINFO_FILENAME);
        $hrefs = $this->stylesheets->resolve($layout, $pageSlug, $parsed['body'], $data);
        $data['stylesheets'] = $this->stylesheets->toHtml($hrefs);

        $html = $this->view->pageFromString($parsed['body'], $data, $layout . '.html');

        return $this->responseFactory->html($html);
    }
}
