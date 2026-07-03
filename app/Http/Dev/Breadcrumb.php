<?php

declare(strict_types=1);

namespace App\Http\Dev;

final class Breadcrumb
{
    /**
     * @param list<array{label: string, href?: string}> $items
     */
    public static function render(array $items): string
    {
        $parts = [];
        $last = array_key_last($items);

        foreach ($items as $i => $item) {
            $label = htmlspecialchars((string) ($item['label'] ?? ''), ENT_QUOTES);
            $href = (string) ($item['href'] ?? '');

            if ($href !== '' && $i !== $last) {
                $parts[] = '<a href="' . htmlspecialchars($href, ENT_QUOTES) . '">' . $label . '</a>';
            } else {
                $parts[] = '<span aria-current="page">' . $label . '</span>';
            }
        }

        return implode('<span class="dev-breadcrumb__sep" aria-hidden="true">/</span>', $parts);
    }
}
