<?php

declare(strict_types=1);

namespace App\Http\Dev\Sections\Defaults;

trait AwardsDefaults
{
    private static function awardsContent(string $variant): array
    {
        return match ($variant) {
            'awards2' => [
                'tagline' => 'Récompenses',
                'items' => self::awards2Items(),
            ],
            default => [
                'title' => 'Récompenses',
                'items' => self::awards1Items(),
            ],
        };
    }

    /**
     * @return list<array<string, string>>
     */
    private static function awards1Items(): array
    {
        return [
            [
                'title' => 'CSS Design Awards',
                'text' => 'Reconnu pour l\'excellence en design web et en fonctionnalité.',
                'label' => '2024',
                'href' => '#',
            ],
            [
                'title' => 'Awwwards Site of the Day',
                'text' => 'Mis en avant pour sa créativité et son innovation en développement web.',
                'label' => '2023',
                'href' => '#',
            ],
            [
                'title' => 'Shadcnblocks UI Blocks',
                'text' => 'Récompensé pour son expérience utilisateur et son interface remarquables.',
                'label' => '2023',
                'href' => 'https://www.shadcnblocks.com',
            ],
            [
                'title' => 'Web Design Excellence',
                'text' => 'Honoré pour la qualité de son design et sa mise en œuvre technique.',
                'label' => '2022',
                'href' => '#',
            ],
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private static function awards2Items(): array
    {
        return [
            ['title' => 'Prix de la collaboration créative', 'label' => '2023'],
            ['title' => 'Prix d\'excellence design', 'label' => '2023'],
            ['title' => 'Innovation en développement web', 'label' => '2022'],
            ['title' => 'Meilleure expérience utilisateur', 'label' => '2022'],
            ['title' => 'Design visuel remarquable', 'label' => '2021'],
            ['title' => 'Prix de l\'innovation digitale', 'label' => '2021'],
            ['title' => 'Excellence technologique créative', 'label' => '2020'],
            ['title' => 'Meilleur design interactif', 'label' => '2020'],
            ['title' => 'Réussite en design web', 'label' => '2019'],
        ];
    }
}
