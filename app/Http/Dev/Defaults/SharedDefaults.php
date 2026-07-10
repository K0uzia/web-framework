<?php

declare(strict_types=1);

namespace App\Http\Dev\Defaults;

use Capsule\SectionAssets;

trait SharedDefaults
{
    private const SHARED = 'hero';
    private const FEATURES_SHARED = 'features';
    private const INTEGRATIONS_SHARED = 'integrations';
    private const PRICING_SHARED = 'pricing';

    /**
     * @return list<array{label: string, href: string, style: string}>
     */
    private static function primarySecondaryButtons(string $primary, string $secondary): array
    {
        return [
            ['label' => $primary, 'href' => '#', 'style' => 'primary'],
            ['label' => $secondary, 'href' => '#', 'style' => 'secondary'],
        ];
    }

    /**
     * @return list<array{url: string, title: string}>
     */
    private static function reviewAvatars(): array
    {
        $names = ['Mia Chen', 'Marcus Rivera', 'Priya Sharma', 'James Okafor', 'Sofia Chen'];
        $avatars = [];
        foreach ($names as $index => $name) {
            $avatars[] = [
                'url' => SectionAssets::shared(self::SHARED, 'avatars/avatar' . ($index + 1) . '.jpg'),
                'title' => $name,
            ];
        }

        return $avatars;
    }
}
