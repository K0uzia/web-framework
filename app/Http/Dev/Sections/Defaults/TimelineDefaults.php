<?php

declare(strict_types=1);

namespace App\Http\Dev\Sections\Defaults;

use Capsule\SectionAssets;

trait TimelineDefaults
{
    private static function timelineContent(string $variant): array
    {
        return match ($variant) {
            'timeline9' => [
                'title' => 'L\'histoire de l\'intelligence artificielle',
                'items' => self::timeline9Items(),
            ],
            default => [
                'title' => 'Découvrez la différence avec nous',
                'subtitle' => 'Nous croyons en des partenariats durables avec nos clients, en misant sur le succès à long terme grâce à l\'innovation collaborative et un accompagnement dédié.',
                'buttons' => self::primarySecondaryButtons('Commencer', 'Réserver une démo'),
                'items' => self::timeline3Items(),
            ],
        };
    }

    /**
     * @return list<array<string, string>>
     */
    private static function timeline3Items(): array
    {
        $images = [
            SectionAssets::shared(self::FEATURES_SHARED, 'placeholder-4.svg'),
            SectionAssets::shared(self::FEATURES_SHARED, 'placeholder-3.svg'),
            SectionAssets::shared(self::FEATURES_SHARED, 'placeholder-2.svg'),
            SectionAssets::shared(self::FEATURES_SHARED, 'placeholder-1.svg'),
        ];
        $entries = [
            [
                'title' => 'Accompagnement dédié',
                'text' => 'Extension de nos opérations dans 5 nouveaux pays, touchant des millions d\'utilisateurs.',
            ],
            [
                'title' => 'Levée de fonds série B',
                'text' => '50 millions d\'euros levés pour accélérer le développement produit.',
            ],
            [
                'title' => 'Lancement produit',
                'text' => 'Mise sur le marché de notre produit phare avec succès.',
            ],
            [
                'title' => 'Création de l\'entreprise',
                'text' => 'Démarrage avec la vision de révolutionner le secteur.',
            ],
        ];
        $items = [];
        foreach ($entries as $index => $entry) {
            $items[] = [
                'title' => $entry['title'],
                'text' => $entry['text'],
                'url' => $images[$index % count($images)],
            ];
        }

        return $items;
    }

    /**
     * @return list<array<string, string>>
     */
    private static function timeline9Items(): array
    {
        return [
            [
                'date' => '1956',
                'title' => 'Naissance de l\'IA',
                'text' => 'Le terme « intelligence artificielle » est inventé lors de la conférence de Dartmouth, marquant le début officiel de l\'IA comme discipline. John McCarthy, Marvin Minsky, Nathaniel Rochester et Claude Shannon organisent cet événement fondateur.',
            ],
            [
                'date' => '1966-1973',
                'title' => 'Premier optimisme et premier hiver de l\'IA',
                'text' => 'Les premières années voient un optimisme marqué avec des programmes comme ELIZA (premier chatbot) et SHRDLU. Au début des années 1970, les financements se tarissent face aux limites de la puissance de calcul et à la complexité de l\'intelligence humaine.',
            ],
            [
                'date' => '1980-1987',
                'title' => 'Systèmes experts et renouveau',
                'text' => 'L\'IA connaît un regain d\'intérêt avec des systèmes experts comme MYCIN (diagnostic médical) et DENDRAL (analyse chimique). Ces systèmes à base de règles imitent la prise de décision humaine dans des domaines précis.',
            ],
            [
                'date' => '1997',
                'title' => 'Deep Blue bat le champion d\'échecs',
                'text' => 'Deep Blue d\'IBM devient le premier système informatique à battre un champion du monde d\'échecs en titre, Garry Kasparov, lors d\'une partie en six manches.',
            ],
        ];
    }
}
