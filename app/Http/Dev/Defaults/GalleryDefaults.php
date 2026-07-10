<?php

declare(strict_types=1);

namespace App\Http\Dev\Defaults;

use Capsule\SectionAssets;

trait GalleryDefaults
{
    private static function galleryContent(string $variant): array
    {
        return match ($variant) {
            'gallery6' => [
                'title' => 'Galerie',
                'demo_label' => 'Réserver une démo',
                'demo_href' => '#',
                'items' => self::gallery6Items(),
            ],
            default => [
                'title' => 'Études de cas',
                'subtitle' => 'Découvrez comment des entreprises et des équipes utilisent des technologies web modernes pour créer des expériences numériques remarquables.',
                'items' => self::gallery4Items(),
            ],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private static function gallery4Items(): array
    {
        $images = [
            SectionAssets::shared('hero', 'saas-hero-1-16x9.png'),
            SectionAssets::shared('hero', 'saas-hero-2-16x9.png'),
            SectionAssets::shared('hero', 'saas-hero-3-16x9.png'),
            SectionAssets::shared('hero', 'saas-hero-4-16x9.png'),
            SectionAssets::shared('hero', 'saas-hero-5-16x9.png'),
        ];

        return [
            [
                'title' => 'shadcn/ui : une bibliothèque de composants moderne',
                'text' => 'Découvrez comment shadcn/ui a transformé les bibliothèques React en offrant une approche unique de distribution et de personnalisation des composants.',
                'href' => 'https://ui.shadcn.com',
                'url' => $images[0],
            ],
            [
                'title' => 'Tailwind CSS : la révolution utility-first',
                'text' => 'Tailwind CSS a changé la façon de styliser les applications web grâce à une approche utility-first qui accélère le développement tout en gardant une grande liberté de design.',
                'href' => 'https://tailwindcss.com',
                'url' => $images[1],
            ],
            [
                'title' => 'Astro : le framework web tout-en-un',
                'text' => 'L\'architecture Islands et l\'approche zero-JS par défaut d\'Astro aident les équipes à livrer des sites plus rapides avec de l\'interactivité ciblée.',
                'href' => 'https://astro.build',
                'url' => $images[2],
            ],
            [
                'title' => 'React : l\'UI par composants',
                'text' => 'React continue de façonner le développement web moderne avec une architecture par composants réutilisables et maintenables.',
                'href' => 'https://react.dev',
                'url' => $images[3],
            ],
            [
                'title' => 'Next.js : React prêt pour la production',
                'text' => 'Next.js est devenu le framework de référence pour les applications React full stack, avec routage fichier, optimisations automatiques et composants serveur.',
                'href' => 'https://nextjs.org',
                'url' => $images[4],
            ],
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private static function gallery6Items(): array
    {
        $image = SectionAssets::shared(self::FEATURES_SHARED, 'placeholder-1.svg');

        return [
            [
                'title' => 'Construire des interfaces modernes',
                'text' => 'Créez des interfaces soignées avec un système de design complet et cohérent.',
                'href' => '#',
                'url' => $image,
            ],
            [
                'title' => 'Technologie de vision par ordinateur',
                'text' => 'Reconnaissance et traitement d\'images pour analyser et interpréter des informations visuelles.',
                'href' => '#',
                'url' => $image,
            ],
            [
                'title' => 'Automatisation par apprentissage automatique',
                'text' => 'Des algorithmes qui apprennent des données pour automatiser des tâches complexes avec peu d\'intervention humaine.',
                'href' => '#',
                'url' => $image,
            ],
            [
                'title' => 'Analytique prédictive',
                'text' => 'Anticipez tendances et résultats à partir de données historiques pour décider en connaissance de cause.',
                'href' => '#',
                'url' => $image,
            ],
            [
                'title' => 'Architecture de réseaux de neurones',
                'text' => 'Des modèles inspirés du cerveau humain, capables de résoudre des problèmes complexes par deep learning.',
                'href' => '#',
                'url' => $image,
            ],
        ];
    }

    /**
     * @return list<array<string, string>>
     */}
