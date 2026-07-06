<?php

declare(strict_types=1);

namespace App\Http\Dev;

/**
 * Modèles de pages préassemblés (équivalent natif des layouts shadcn Pages).
 */
final class PageTemplates
{
    /**
     * @return list<array{id: string, label: string, description: string, icon: string}>
     */
    public static function all(): array
    {
        return [
            [
                'id' => 'blank',
                'label' => 'Page vide',
                'description' => 'Aucun bloc, vous composez la page vous-même.',
                'icon' => 'fa-solid fa-file',
            ],
            [
                'id' => 'landing',
                'label' => 'Page d\'accueil',
                'description' => 'Hero, logos, fonctionnalités, étapes, témoignages, inscription et CTA.',
                'icon' => 'fa-solid fa-rocket',
            ],
            [
                'id' => 'pricing',
                'label' => 'Tarifs',
                'description' => 'Hero, grille tarifaire, FAQ et conversion.',
                'icon' => 'fa-solid fa-tags',
            ],
            [
                'id' => 'about',
                'label' => 'À propos',
                'description' => 'Présentation, chiffres clés, équipe et CTA.',
                'icon' => 'fa-solid fa-building',
            ],
            [
                'id' => 'contact',
                'label' => 'Contact',
                'description' => 'Accroche, coordonnées et questions fréquentes.',
                'icon' => 'fa-solid fa-envelope',
            ],
            [
                'id' => 'faq',
                'label' => 'FAQ',
                'description' => 'Hero centré, accordéon de questions et CTA.',
                'icon' => 'fa-solid fa-circle-question',
            ],
            [
                'id' => 'feature',
                'label' => 'Fonctionnalités',
                'description' => 'Hero, grille de points clés, stats et conversion.',
                'icon' => 'fa-solid fa-table-cells-large',
            ],
            [
                'id' => 'services',
                'label' => 'Services',
                'description' => 'Étapes, fonctionnalités, comparaison et appel à l\'action.',
                'icon' => 'fa-solid fa-briefcase',
            ],
            [
                'id' => 'integrations',
                'label' => 'Intégrations',
                'description' => 'Logos partenaires, points clés et preuve sociale.',
                'icon' => 'fa-solid fa-plug',
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function sections(string $templateId): array
    {
        return match ($templateId) {
            'landing' => self::landing(),
            'pricing' => self::pricing(),
            'about' => self::about(),
            'contact' => self::contact(),
            'faq' => self::faq(),
            'feature' => self::feature(),
            'services' => self::services(),
            'integrations' => self::integrations(),
            default => [],
        };
    }

    public static function buildOptionsHtml(string $selected = 'blank'): string
    {
        $options = [];
        foreach (self::all() as $template) {
            $id = $template['id'];
            $options[] = '<option value="' . htmlspecialchars($id, ENT_QUOTES) . '"'
                . ($id === $selected ? ' selected' : '') . '>'
                . htmlspecialchars($template['label'], ENT_QUOTES) . '</option>';
        }

        return implode('', $options);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function landing(): array
    {
        return [
            self::section('hero', 'centered'),
            self::section('logos', 'row'),
            self::section('features', 'grid-3'),
            self::section('steps', 'row'),
            self::section('testimonials', 'grid'),
            self::section('newsletter', 'centered'),
            self::section('cta', 'banner'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function pricing(): array
    {
        return [
            self::section('hero', 'centered', [
                'title' => 'Des tarifs simples et transparents',
                'subtitle' => 'Choisissez la formule adaptée à votre équipe.',
                'buttons' => [['label' => 'Voir les offres', 'href' => '#tarifs', 'style' => 'primary']],
            ]),
            self::section('pricing', 'cards'),
            self::section('compare', 'table'),
            self::section('faq', 'list'),
            self::section('cta', 'banner'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function about(): array
    {
        return [
            self::section('hero', 'centered', [
                'title' => 'À propos de nous',
                'subtitle' => 'Notre mission, notre équipe et ce qui nous anime.',
                'buttons' => [],
            ]),
            self::section('about', 'split'),
            self::section('stats', 'row'),
            self::section('team', 'grid'),
            self::section('cta', 'banner'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function contact(): array
    {
        return [
            self::section('hero', 'centered', [
                'title' => 'Contactez-nous',
                'subtitle' => 'Une question ? Nous répondons sous 24 h ouvrées.',
                'buttons' => [],
            ]),
            self::section('contact', 'cards'),
            self::section('faq', 'list'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function faq(): array
    {
        return [
            self::section('hero', 'centered', [
                'title' => 'Questions fréquentes',
                'subtitle' => 'Tout ce qu\'il faut savoir avant de commencer.',
                'buttons' => [],
            ]),
            self::section('faq', 'list'),
            self::section('cta', 'banner', [
                'title' => 'Une autre question ?',
                'buttons' => [['label' => 'Nous écrire', 'href' => '#', 'style' => 'primary']],
            ]),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function feature(): array
    {
        return [
            self::section('hero', 'split'),
            self::section('features', 'grid-3'),
            self::section('stats', 'row'),
            self::section('cta', 'banner'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function services(): array
    {
        return [
            self::section('hero', 'centered', [
                'title' => 'Des services pensés pour vous',
                'subtitle' => 'Un parcours clair, de la prise de contact à la livraison.',
                'buttons' => [['label' => 'Demander un devis', 'href' => '#', 'style' => 'primary']],
            ]),
            self::section('steps', 'row'),
            self::section('features', 'grid-3'),
            self::section('compare', 'table'),
            self::section('cta', 'banner'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function integrations(): array
    {
        return [
            self::section('hero', 'centered', [
                'title' => 'S\'intègre à vos outils',
                'subtitle' => 'Connectez votre stack en quelques clics.',
                'buttons' => [],
            ]),
            self::section('logos', 'row'),
            self::section('features', 'grid-3'),
            self::section('testimonials', 'grid'),
            self::section('cta', 'banner'),
        ];
    }

    /**
     * @param array<string, mixed>|null $contentOverride
     *
     * @return array<string, mixed>
     */
    private static function section(string $type, string $variant, ?array $contentOverride = null): array
    {
        return [
            'id' => $type . '-' . bin2hex(random_bytes(3)),
            'type' => $type,
            'variant' => $variant,
            'visible' => true,
            'content' => $contentOverride ?? SectionDefaults::content($type),
            'style' => SectionDefaults::style($type),
        ];
    }
}
