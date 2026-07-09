<?php

declare(strict_types=1);

namespace Capsule;

final class PageRegistry
{
    public function __construct(private readonly PageRepository $pages)
    {
    }

    /**
     * @return array{
     *   static: array<string, PageRoute>,
     *   dynamic: list<PageRoute>
     * }
     */
    public function routes(): array
    {
        $static = [];
        foreach ($this->pages->allPublished() as $page) {
            $path = $page->routePath();
            $static['GET ' . $path] = new PageRoute($page->slug, '#^' . preg_quote($path, '#') . '$#');
        }

        return ['static' => $static, 'dynamic' => []];
    }
}
