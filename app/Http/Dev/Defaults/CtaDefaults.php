<?php

declare(strict_types=1);

namespace App\Http\Dev\Defaults;

use Capsule\SectionAssets;

trait CtaDefaults
{
    private static function ctaContent(string $variant): array
    {
        $base = [
            'title' => 'Passez à l\'action',
            'subtitle' => 'Accédez dès aujourd\'hui à notre bibliothèque de blocs et composants prêts à l\'emploi.',
        ];

        return match ($variant) {
            'cta4' => $base + [
                'buttons' => [
                    ['label' => 'Commencer', 'href' => '#', 'style' => 'primary'],
                ],
                'items' => self::cta4Features(),
            ],
            'cta10', 'cta36' => $base + [
                'buttons' => self::ctaButtonsSecondaryFirst(),
            ],
            'cta11' => $base + [
                'buttons' => [
                    ['label' => 'Accéder', 'href' => '#', 'style' => 'primary'],
                ],
                'image_url' => SectionAssets::shared('hero', 'saas-hero-1-16x9.png'),
                'icon' => 'fa-wand-magic-sparkles',
            ],
            default => $base + [
                'buttons' => self::ctaButtonsPrimaryFirst(),
            ],
        };
    }

    /**
     * @return list<array<string, string>>
     */
    private static function cta4Features(): array
    {
        return [
            ['title' => 'Intégration simple'],
            ['title' => 'Support 24/7'],
            ['title' => 'Design personnalisable'],
            ['title' => 'Performances évolutives'],
            ['title' => 'Des centaines de blocs'],
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private static function ctaButtonsPrimaryFirst(): array
    {
        return [
            ['label' => 'Accéder', 'href' => '#', 'style' => 'primary'],
            ['label' => 'Planifier une démo', 'href' => '#', 'style' => 'secondary'],
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private static function ctaButtonsSecondaryFirst(): array
    {
        return [
            ['label' => 'Planifier une démo', 'href' => '#', 'style' => 'secondary'],
            ['label' => 'Accéder', 'href' => '#', 'style' => 'primary'],
        ];
    }
}
