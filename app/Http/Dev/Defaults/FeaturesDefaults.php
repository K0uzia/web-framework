<?php

declare(strict_types=1);

namespace App\Http\Dev\Defaults;

use Capsule\SectionAssets;

trait FeaturesDefaults
{
    private static function featuresContent(string $variant): array
    {
        $base = [
            'title' => 'Construisez plus vite avec des fonctionnalités prêtes pour la production',
            'subtitle' => 'Chaque composant est pensé pour React et Tailwind. Copiez, adaptez et publiez en quelques minutes.',
            'label' => 'Fonctionnalités',
            'badge' => 'Badge',
            'image_url' => SectionAssets::shared(self::FEATURES_SHARED, 'saas-detail-1-1x1.png'),
            'image_alt' => 'Aperçu des blocs',
            'overlay_date' => '2025 | Mars',
            'overlay_title' => "Nouvelle\nCollection",
            'overlay_text' => 'Découvrez notre dernière série de composants soignés.',
            'overlay_link' => '#',
            'overlay_link_label' => 'Tout voir',
            'items' => self::featureCardItems(),
            'buttons' => self::primarySecondaryButtons('Parcourir les composants', 'Voir la démo'),
        ];

        return match ($variant) {
            'feature1', 'feature2' => array_merge($base, [
                'title' => 'Des blocs prêts à intégrer avec shadcn/ui',
                'subtitle' => 'Sections React prêtes pour la production, construites avec Tailwind et shadcn/ui.',
                'buttons' => [['label' => 'Voir la fonctionnalité', 'href' => '#', 'style' => 'secondary']],
            ]),
            'feature74' => array_merge($base, [
                'title' => 'Nom de la fonctionnalité',
                'subtitle' => 'Texte d\'introduction pour présenter la valeur de votre produit en quelques phrases.',
                'buttons' => [['label' => 'Réserver une démo', 'href' => '#', 'style' => 'primary']],
                'items' => array_slice(self::featureCardItems(), 0, 2),
            ]),
            'feature166' => array_merge($base, [
                'title' => 'Blocs construits avec Shadcn et Tailwind',
                'subtitle' => 'Composants soignés en React, Tailwind et shadcn/ui. Copiez et personnalisez directement dans votre projet.',
                'items' => self::bentoItems(),
            ]),
            'feature197' => array_merge($base, [
                'title' => 'Fonctionnalités',
                'items' => self::accordionItems(),
            ]),
            'feature239' => array_merge($base, [
                'title' => "Transformez une idée\nen réalité",
                'subtitle' => 'Libérez votre créativité dans un espace de travail intuitif. Imaginez, concevez et livrez sans friction.',
                'buttons' => [['label' => 'Parcourir les composants', 'href' => '#', 'style' => 'secondary']],
                'image_url' => SectionAssets::shared(self::FEATURES_SHARED, 'images/1-1x1.jpg'),
            ]),
            'feature51' => array_merge($base, [
                'items' => self::tabItems(),
            ]),
            default => $base,
        };
    }

    /**
     * @return array<string, mixed>
     */
    private static function featureCardItems(): array
    {
        $titles = [
            ['Code source complet', 'Chaque bloc est du React que vous possédez. Aucune dépendance runtime.'],
            ['Design responsive', 'Adaptation fluide du mobile au desktop avec Tailwind.'],
            ['Personnalisable', 'Remplacez icônes, espacements et contenus sans verrouillage.'],
            ['Prêt pour la production', 'Code éprouvé, sans placeholder ni lorem ipsum.'],
            ['Compatible registry', 'Installation directe via la CLI shadcn.'],
            ['Framework agnostique', 'Fonctionne avec Next.js, Vite, Remix et Astro.'],
        ];
        $items = [];
        foreach ($titles as $i => [$title, $text]) {
            $items[] = [
                'title' => $title,
                'text' => $text,
                'label' => sprintf('%02d', $i + 1),
                'url' => SectionAssets::shared(self::FEATURES_SHARED, 'saas-card-detail-' . ($i + 1) . '-4x3.svg'),
                'href' => '#',
            ];
        }

        return $items;
    }

    /**
     * @return list<array<string, string>>
     */
    private static function bentoItems(): array
    {
        return [
            [
                'title' => 'UI/UX Design',
                'text' => 'Expériences intuitives avec des principes de design centrés utilisateur.',
                'url' => SectionAssets::shared(self::FEATURES_SHARED, 'placeholder-1.svg'),
            ],
            [
                'title' => 'Développement responsive',
                'text' => 'Sites qui s\'adaptent parfaitement à tous les écrans.',
                'url' => SectionAssets::shared(self::FEATURES_SHARED, 'placeholder-2.svg'),
            ],
            [
                'title' => 'Intégration de marque',
                'text' => 'Votre identité visuelle intégrée dans chaque détail.',
                'url' => SectionAssets::shared(self::FEATURES_SHARED, 'placeholder-3.svg'),
            ],
            [
                'title' => 'Optimisation performance',
                'text' => 'Chargement rapide grâce à un code et des assets optimisés.',
                'url' => SectionAssets::shared(self::FEATURES_SHARED, 'placeholder-4.svg'),
            ],
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private static function accordionItems(): array
    {
        $entries = [
            ['Blocs shadcn/ui', 'bw12.jpeg', 'Parcourez notre collection de blocs UI prêts à l\'emploi, responsive et accessibles.'],
            ['Tailwind et TypeScript', 'bw15.jpeg', 'Styling rapide et typage strict pour un code fiable en production.'],
            ['Mode sombre et personnalisation', 'bw20.jpeg', 'Chaque bloc supporte le dark mode et s\'adapte à votre thème.'],
            ['Accessibilité d\'abord', 'bw21.jpeg', 'ARIA, navigation clavier et HTML sémantique intégrés.'],
        ];
        $items = [];
        foreach ($entries as [$title, $file, $text]) {
            $items[] = [
                'title' => $title,
                'text' => $text,
                'url' => SectionAssets::shared(self::FEATURES_SHARED, 'lummi/' . $file),
            ];
        }

        return $items;
    }

    /**
     * @return list<array<string, string|bool>>
     */
    private static function tabItems(): array
    {
        return [
            [
                'title' => 'Recherche',
                'text' => 'Découvrez les fonctionnalités qui distinguent notre plateforme.',
                'url' => SectionAssets::shared(self::FEATURES_SHARED, 'placeholder-1.svg'),
                'href' => '#',
                'is_default' => true,
            ],
            [
                'title' => 'Affinage',
                'text' => 'Technologie récente pensée pour la productivité.',
                'url' => SectionAssets::shared(self::FEATURES_SHARED, 'placeholder-2.svg'),
                'href' => '#',
            ],
            [
                'title' => 'Construction',
                'text' => 'Créez des expériences avec notre boîte à outils complète.',
                'url' => SectionAssets::shared(self::FEATURES_SHARED, 'placeholder-3.svg'),
                'href' => '#',
            ],
        ];
    }
}
