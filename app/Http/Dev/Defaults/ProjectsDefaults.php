<?php

declare(strict_types=1);

namespace App\Http\Dev\Defaults;

use Capsule\SectionAssets;

trait ProjectsDefaults
{
    private static function projectsContent(string $variant): array
    {
        return match ($variant) {
            default => [
                'title' => 'Projets',
                'items' => self::projects5Items(),
            ],
        };
    }

    /**
     * @return list<array<string, string>>
     */
    private static function projects5Items(): array
    {
        $images = [
            SectionAssets::shared(self::FEATURES_SHARED, 'lummi/bw12.jpeg'),
            SectionAssets::shared(self::FEATURES_SHARED, 'lummi/bw15.jpeg'),
            SectionAssets::shared(self::FEATURES_SHARED, 'lummi/bw20.jpeg'),
            SectionAssets::shared(self::FEATURES_SHARED, 'lummi/bw21.jpeg'),
            SectionAssets::shared(self::FEATURES_SHARED, 'images/1-1x1.jpg'),
            SectionAssets::shared(self::FEATURES_SHARED, 'saas-detail-1-1x1.png'),
        ];
        $entries = [
            ['title' => 'Pavillon en béton moderne', 'label' => 'Architecture'],
            ['title' => 'Habitat urbain coloré', 'label' => 'Design urbain'],
            ['title' => 'Retraite minimaliste', 'label' => 'Intérieur'],
            ['title' => 'Maison urbaine en béton', 'label' => 'Design produit'],
            ['title' => 'Volume luxe en béton', 'label' => 'Résidentiel'],
            ['title' => 'Verrière en nature', 'label' => 'Design durable'],
        ];
        $items = [];
        foreach ($entries as $index => $entry) {
            $items[] = [
                'title' => $entry['title'],
                'label' => $entry['label'],
                'date' => '2025',
                'href' => '#',
                'url' => $images[$index % count($images)],
            ];
        }

        return $items;
    }

    /**
     * @return array<string, mixed>
     */}
