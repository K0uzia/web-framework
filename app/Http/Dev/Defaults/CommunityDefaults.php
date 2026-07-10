<?php

declare(strict_types=1);

namespace App\Http\Dev\Defaults;

use Capsule\SectionAssets;

trait CommunityDefaults
{
    private static function communityContent(string $variant): array
    {
        return match ($variant) {
            'community2' => [
                'title' => 'Rejoignez notre communauté',
                'subtitle' => 'Échangez avec les autres, partagez vos expériences et restez informé.',
                'items' => self::community2Items(),
            ],
            default => [
                'title' => 'Rejoignez notre communauté',
                'subtitle' => 'de designers et développeurs',
                'image_url' => SectionAssets::shared('hero', 'block-1.svg'),
                'items' => self::community1Items(),
            ],
        };
    }

    /**
     * @return list<array<string, string>>
     */
    private static function community1Items(): array
    {
        return [
            ['title' => 'X', 'icon' => 'twitter', 'href' => 'https://x.com/shadcnblocks'],
            ['title' => 'GitHub', 'icon' => 'github', 'href' => 'https://github.com/shadcnblocks'],
            ['title' => 'Discord', 'icon' => 'discord', 'href' => '#'],
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private static function community2Items(): array
    {
        return [
            [
                'title' => 'X',
                'text' => 'Suivez nos dernières actualités et annonces.',
                'icon' => 'twitter',
                'href' => 'https://x.com/shadcnblocks',
            ],
            [
                'title' => 'LinkedIn',
                'text' => 'Connectez-vous avec nous et explorez les opportunités.',
                'icon' => 'linkedin',
                'href' => 'https://www.linkedin.com/company/shadcnblocks',
            ],
            [
                'title' => 'GitHub',
                'text' => 'Contribuez à nos projets open source.',
                'icon' => 'github',
                'href' => 'https://github.com/shadcnblocks',
            ],
            [
                'title' => 'Discord',
                'text' => 'Rejoignez notre serveur et échangez avec d\'autres développeurs.',
                'icon' => 'discord',
                'href' => '#',
            ],
        ];
    }
}
