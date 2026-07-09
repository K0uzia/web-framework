<?php

declare(strict_types=1);

namespace App\Http\Dev;

use Capsule\ChromeVariants;
use Capsule\HeaderStyle;

/**
 * Modèles par défaut pour les variantes d'en-tête shadcnblocks.
 */
final class HeaderTemplates
{
    /**
     * @return list<array{id: string, label: string, description: string, icon: string}>
     */
    public static function definitions(): array
    {
        return [
            [
                'id' => HeaderStyle::TEMPLATE_DEFAULT,
                'label' => 'Classique',
                'description' => 'Marque, navigation et boutons sur trois zones.',
                'icon' => 'fa-solid fa-table-columns',
            ],
            [
                'id' => 'navbar1',
                'label' => 'Navbar 1',
                'description' => 'Menu avec sous-menus détaillés et actions à droite.',
                'icon' => 'fa-solid fa-bars-staggered',
            ],
            [
                'id' => 'navbar5',
                'label' => 'Navbar 5',
                'description' => 'Méga-menu fonctionnalités et liens centrés.',
                'icon' => 'fa-solid fa-grip',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function create(string $template, string $name): array
    {
        $template = HeaderStyle::normalizeTemplate($template);
        if ($template === HeaderStyle::TEMPLATE_DEFAULT) {
            return ChromeVariants::normalizeHeader([
                'id' => ChromeVariants::newId(),
                'name' => $name,
                'template' => HeaderStyle::TEMPLATE_DEFAULT,
            ]);
        }

        $preset = $template === 'navbar5' ? self::navbar5Preset() : self::navbar1Preset();
        $preset['id'] = ChromeVariants::newId();
        $preset['name'] = $name;
        $preset['template'] = $template;

        return ChromeVariants::normalizeHeader($preset);
    }

    /**
     * @return array<string, mixed>
     */
    private static function navbar1Preset(): array
    {
        return [
            'menu_items' => [
                ['label' => 'Accueil', 'href' => '/'],
                [
                    'label' => 'Produits',
                    'href' => '#',
                    'children' => [
                        ['label' => 'Blog', 'description' => 'Actualités et nouveautés du produit', 'href' => '#', 'icon' => 'book'],
                        ['label' => 'Entreprise', 'description' => 'Notre mission et notre équipe', 'href' => '#', 'icon' => 'tree'],
                        ['label' => 'Carrières', 'description' => 'Rejoignez nos équipes', 'href' => '#', 'icon' => 'sun'],
                        ['label' => 'Support', 'description' => 'Contactez notre équipe support', 'href' => '#', 'icon' => 'zap'],
                    ],
                ],
                [
                    'label' => 'Ressources',
                    'href' => '#',
                    'children' => [
                        ['label' => 'Centre d\'aide', 'description' => 'Réponses à vos questions', 'href' => '#', 'icon' => 'zap'],
                        ['label' => 'Contact', 'description' => 'Nous écrire directement', 'href' => '#', 'icon' => 'sun'],
                    ],
                ],
                ['label' => 'Tarifs', 'href' => '#'],
                ['label' => 'Blog', 'href' => '#'],
            ],
            'login' => ['enabled' => true, 'label' => 'Connexion', 'href' => '/login', 'style' => 'outline'],
            'cta' => ['enabled' => true, 'label' => 'Commencer', 'href' => '#', 'style' => 'primary'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function navbar5Preset(): array
    {
        return [
            'features_label' => 'Fonctionnalités',
            'features' => [
                ['title' => 'Tableau de bord', 'description' => 'Vue d\'ensemble de votre activité', 'href' => '#'],
                ['title' => 'Analytique', 'description' => 'Suivez vos performances', 'href' => '#'],
                ['title' => 'Paramètres', 'description' => 'Configurez vos préférences', 'href' => '#'],
                ['title' => 'Intégrations', 'description' => 'Connectez vos outils', 'href' => '#'],
                ['title' => 'Stockage', 'description' => 'Gérez vos fichiers', 'href' => '#'],
                ['title' => 'Support', 'description' => 'Obtenez de l\'aide', 'href' => '#'],
            ],
            'nav_links' => [
                ['label' => 'Produits', 'href' => '#'],
                ['label' => 'Ressources', 'href' => '#'],
                ['label' => 'Contact', 'href' => '#'],
            ],
            'mobile_links' => [
                ['label' => 'Modèles', 'href' => '#'],
                ['label' => 'Blog', 'href' => '#'],
                ['label' => 'Tarifs', 'href' => '#'],
            ],
            'login' => ['enabled' => true, 'label' => 'Connexion', 'href' => '/login', 'style' => 'outline'],
            'cta' => ['enabled' => true, 'label' => 'Essai gratuit', 'href' => '#', 'style' => 'primary'],
        ];
    }
}
