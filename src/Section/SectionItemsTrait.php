<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\SectionAssets;

trait SectionItemsTrait
{
    /**
     * @param array<string, mixed> $content
     *
     * @return list<array<string, mixed>>
     */
    protected static function itemsFromContent(array $content, int $max): array
    {
        $raw = is_array($content['items'] ?? null) ? $content['items'] : [];
        $items = [];
        foreach (array_slice($raw, 0, $max) as $item) {
            if (is_array($item)) {
                $items[] = $item;
            }
        }

        return $items;
    }

    protected static function hrefFromItem(string $href): string
    {
        $trimmed = trim($href);

        return htmlspecialchars($trimmed !== '' ? $trimmed : '#', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    protected static function imageUrlFromItem(string $url, int $index, string $sharedType, string $fallbackFile): string
    {
        return SectionAssets::resolve(
            $url,
            SectionAssets::shared($sharedType, $fallbackFile),
        );
    }
}
