<?php

declare(strict_types=1);

namespace App\Http\Dev;

use Capsule\ChromeVariants;
use Capsule\FooterStyle;

/**
 * Modèles par défaut pour les variantes de pied de page shadcnblocks.
 */
final class FooterTemplates
{
    /**
     * @return list<array{id: string, label: string, description: string, icon: string}>
     */
    public static function definitions(): array
    {
        return [
            [
                'id' => FooterStyle::TEMPLATE_DEFAULT,
                'label' => 'Classique',
                'description' => 'Marque, navigation et mentions sur deux zones.',
                'icon' => 'fa-solid fa-table-columns',
            ],
            [
                'id' => 'footer2',
                'label' => 'Footer 2',
                'description' => 'Logo, description et quatre colonnes de liens.',
                'icon' => 'fa-solid fa-table-cells-large',
            ],
            [
                'id' => 'footer7',
                'label' => 'Footer 7',
                'description' => 'Bloc marque avec réseaux sociaux et trois colonnes.',
                'icon' => 'fa-solid fa-share-nodes',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function create(string $template, string $name): array
    {
        $template = FooterStyle::normalizeTemplate($template);
        if ($template === FooterStyle::TEMPLATE_DEFAULT) {
            return ChromeVariants::normalizeFooter([
                'id' => ChromeVariants::newId(),
                'name' => $name,
                'template' => FooterStyle::TEMPLATE_DEFAULT,
            ]);
        }

        $preset = $template === 'footer7' ? self::footer7Preset() : self::footer2Preset();
        $preset['id'] = ChromeVariants::newId();
        $preset['name'] = $name;
        $preset['template'] = $template;

        return ChromeVariants::normalizeFooter($preset);
    }

    /**
     * @return array<string, mixed>
     */
    private static function footer2Preset(): array
    {
        return [
            'description' => 'Blocs soignés pour vos pages marketing, prêts à intégrer.',
            'sections' => self::productSections(4),
            'legal_links' => self::legalLinks(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function footer7Preset(): array
    {
        return [
            'description' => 'Blocs soignés pour vos pages marketing, prêts à intégrer.',
            'sections' => self::productSections(3),
            'legal_links' => self::legalLinks(),
            'social_links' => [
                ['network' => 'instagram', 'href' => '#'],
                ['network' => 'facebook', 'href' => '#'],
                ['network' => 'x', 'href' => '#'],
                ['network' => 'linkedin', 'href' => '#'],
                ['network' => 'github', 'href' => '#'],
            ],
        ];
    }

    /**
     * @return list<array{title: string, links: list<array{label: string, href: string}>}>
     */
    private static function productSections(int $count): array
    {
        $all = [
            [
                'title' => 'Produit',
                'links' => [
                    ['label' => 'Aperçu', 'href' => '#'],
                    ['label' => 'Tarifs', 'href' => '#'],
                    ['label' => 'Marketplace', 'href' => '#'],
                    ['label' => 'Fonctionnalités', 'href' => '#'],
                    ['label' => 'Intégrations', 'href' => '#'],
                ],
            ],
            [
                'title' => 'Entreprise',
                'links' => [
                    ['label' => 'À propos', 'href' => '#'],
                    ['label' => 'Équipe', 'href' => '#'],
                    ['label' => 'Blog', 'href' => '#'],
                    ['label' => 'Carrières', 'href' => '#'],
                    ['label' => 'Contact', 'href' => '#'],
                ],
            ],
            [
                'title' => 'Support',
                'links' => [
                    ['label' => 'Centre d\'aide', 'href' => '#'],
                    ['label' => 'Documentation', 'href' => '#'],
                    ['label' => 'Statut', 'href' => '#'],
                    ['label' => 'Communauté', 'href' => '#'],
                ],
            ],
            [
                'title' => 'Ressources',
                'links' => [
                    ['label' => 'Guides', 'href' => '#'],
                    ['label' => 'Modèles', 'href' => '#'],
                    ['label' => 'Ventes', 'href' => '#'],
                    ['label' => 'Publicité', 'href' => '#'],
                ],
            ],
        ];

        return array_slice($all, 0, $count);
    }

    /**
     * @return list<array{label: string, href: string}>
     */
    private static function legalLinks(): array
    {
        return [
            ['label' => 'Conditions générales', 'href' => '#'],
            ['label' => 'Politique de confidentialité', 'href' => '#'],
        ];
    }
}
