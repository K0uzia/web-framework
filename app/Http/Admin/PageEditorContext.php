<?php

declare(strict_types=1);

namespace App\Http\Admin;

use Capsule\Page;

final class PageEditorContext
{
    /**
     * @return array{
     *     page_slug_display: string,
     *     page_updated_relative: string,
     *     page_updated_absolute: string,
     * }
     */
    public static function sidebar(Page $page): array
    {
        $slugDisplay = $page->slug === '' ? '/' : '/' . $page->slug;

        return [
            'page_slug_display' => $slugDisplay,
            'page_updated_relative' => RelativeTime::format($page->updatedAt),
            'page_updated_absolute' => RelativeTime::absolute($page->updatedAt),
        ];
    }
}
