<?php

declare(strict_types=1);

namespace App\Http\Dev\Sections\Defaults;

use Capsule\SectionAssets;

trait LogosDefaults
{
    private static function logosContent(string $variant): array
    {
        $withTitle = in_array($variant, ['logos3', 'logos8'], true);
        $maxItems = in_array($variant, ['logos8', 'logos18'], true) ? 6 : 12;
        $content = [
            'items' => self::logoItems($maxItems),
        ];
        if ($withTitle) {
            $content['title'] = 'Ils nous font confiance';
        }

        return $content;
    }

    /**
     * @return list<array<string, string>>
     */
    private static function logoItems(int $count): array
    {
        $items = [];
        for ($i = 1; $i <= $count; $i++) {
            $items[] = [
                'title' => 'Logo entreprise ' . $i,
                'url' => SectionAssets::shared(self::SHARED, 'logos/fictional-company-logo-' . $i . '.svg'),
                'href' => '#',
            ];
        }

        return $items;
    }
}
