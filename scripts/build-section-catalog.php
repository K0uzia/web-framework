<?php

declare(strict_types=1);

/**
 * Génère le catalogue marketing (registry, HTML, CSS) aligné sur la taxonomie shadcnblocks.
 *
 * Usage: php scripts/build-section-catalog.php
 */

require dirname(__DIR__) . '/vendor/autoload.php';

use Capsule\SectionLayoutFamilies;
use Capsule\YamlData;

$root = dirname(__DIR__);
$registryPath = $root . '/resources/sections/registry.yaml';
$sectionsDir = $root . '/resources/sections';
$cssDir = $root . '/public/assets/css/sections';

/** @var list<string> */
const GROUP_ORDER = [
    'hero', 'feature', 'integration', 'about', 'content', 'gallery', 'pricing', 'compare',
    'cta', 'newsletter', 'testimonial', 'stats', 'logos', 'team', 'faq', 'contact',
    'blog', 'project', 'timeline', 'service', 'auth', 'career', 'compliance', 'case-study',
    'changelog', 'community', 'download', 'industry', 'list', 'experience', 'process',
    'waitlist', 'award', 'resource', 'code', 'demo',
];

/** Comptage picker : une carte par variante si plusieurs variantes. */

final class CatalogYamlWriter
{
    /**
     * @param array<string, mixed> $data
     */
    public static function dump(array $data): string
    {
        $lines = [];
        foreach ($data as $key => $value) {
            self::dumpKey($lines, (string) $key, $value, 0);
        }

        return implode("\n", $lines) . "\n";
    }

    /**
     * @param list<string> $lines
     * @param mixed $value
     */
    private static function dumpKey(array &$lines, string $key, $value, int $indent): void
    {
        $pad = str_repeat(' ', $indent);
        if (!is_array($value)) {
            $lines[] = $pad . $key . ': ' . self::scalar($value);

            return;
        }
        if ($value === []) {
            $lines[] = $pad . $key . ': []';

            return;
        }
        if (self::isList($value)) {
            $lines[] = $pad . $key . ': ' . self::inlineList($value);

            return;
        }
        $lines[] = $pad . $key . ':';
        foreach ($value as $childKey => $childValue) {
            self::dumpKey($lines, (string) $childKey, $childValue, $indent + 2);
        }
    }

    /**
     * @param array<int, mixed> $list
     */
    private static function inlineList(array $list): string
    {
        $parts = [];
        foreach ($list as $item) {
            $parts[] = self::scalar($item);
        }

        return '[' . implode(', ', $parts) . ']';
    }

    /**
     * @param array<mixed> $arr
     */
    private static function isList(array $arr): bool
    {
        if ($arr === []) {
            return true;
        }
        return array_keys($arr) === range(0, count($arr) - 1);
    }

    /**
     * @param mixed $value
     */
    private static function scalar($value): string
    {
        if (!is_string($value) && !is_int($value) && !is_float($value) && !is_bool($value)) {
            return '""';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        $s = (string) $value;
        if ($s === '' || preg_match('/[:#\[\]{}&,\*\?!|>\'"%@`]/', $s) === 1 || str_starts_with($s, ' ')) {
            return "'" . str_replace("'", "''", $s) . "'";
        }

        return $s;
    }
}

/**
 * @return array<string, array{label: string, description: string}>
 */
function gridFeatureVariants(string $subject): array
{
    return [
        'grid-2' => [
            'label' => 'Grille 2 colonnes',
            'description' => 'Cartes ' . $subject . ' sur deux colonnes responsives.',
        ],
        'grid-3' => [
            'label' => 'Grille 3 colonnes',
            'description' => 'Disposition équilibrée en trois colonnes.',
        ],
        'grid-4' => [
            'label' => 'Grille 4 colonnes',
            'description' => 'Vue dense en quatre colonnes sur grand écran.',
        ],
        'bento' => [
            'label' => 'Bento',
            'description' => 'Grille asymétrique type bento pour mettre un élément en avant.',
        ],
        'list' => [
            'label' => 'Liste',
            'description' => 'Liste verticale avec titres et textes alignés.',
        ],
    ];
}

/**
 * @return array<string, mixed>
 */
function styleFieldsStandard(bool $withAlign = false): array
{
    $fields = [
        'bg' => ['type' => 'color-token', 'label' => 'Fond'],
        'padding' => [
            'type' => 'select',
            'label' => 'Espacement',
            'options' => ['sm', 'md', 'lg', 'xl'],
        ],
    ];
    if ($withAlign) {
        $fields['text_align'] = [
            'type' => 'select',
            'label' => 'Alignement',
            'options' => ['left', 'center', 'right'],
        ];
    }

    return $fields;
}

/**
 * @return array<string, mixed>
 */
function contentRepeaterTitleText(string $itemsLabel = 'Éléments'): array
{
    return [
        'title' => ['type' => 'text', 'label' => 'Titre de section', 'client_editable' => true],
        'subtitle' => ['type' => 'textarea', 'label' => 'Sous-titre de section', 'client_editable' => true],
        'items' => [
            'type' => 'repeater',
            'label' => $itemsLabel,
            'client_editable' => true,
            'fields' => [
                'title' => ['type' => 'text', 'label' => 'Titre'],
                'text' => ['type' => 'textarea', 'label' => 'Texte'],
            ],
        ],
    ];
}

/**
 * @return array<string, array<string, mixed>>
 */
function buildCatalogSpec(): array
{
    $spec = [];

    $spec['hero'] = [
        'prototype' => 'hero',
        'label' => 'Hero',
        'group' => 'hero',
        'icon' => 'fa-solid fa-panorama',
        'description' => 'Grand titre d\'introduction avec accroche et boutons d\'action.',
        'variants' => [
            'centered' => ['label' => 'Centré', 'description' => 'Titre et boutons centrés, mise en page compacte.'],
            'split' => ['label' => 'Deux colonnes', 'description' => 'Texte à gauche, visuel à droite.'],
            'split-left' => ['label' => 'Colonnes inversées', 'description' => 'Visuel à gauche, texte à droite.'],
            'fullscreen' => ['label' => 'Plein écran', 'description' => 'Hauteur maximale pour un impact visuel fort.'],
            'image-below' => ['label' => 'Image en dessous', 'description' => 'Accroche centrée avec visuel sous le texte.'],
            'badge' => ['label' => 'Avec badge', 'description' => 'Badge d\'annonce au-dessus du titre principal.'],
            'minimal' => ['label' => 'Minimal', 'description' => 'Hero épuré sans éléments superflus.'],
            'video' => ['label' => 'Vidéo', 'description' => 'Zone média large pour une vidéo ou un visuel hero.'],
        ],
        'preserve_fields' => true,
    ];

    $spec['features'] = [
        'prototype' => 'features',
        'label' => 'Fonctionnalités',
        'group' => 'feature',
        'icon' => 'fa-solid fa-table-cells-large',
        'description' => 'Grille de points clés avec titre et texte.',
        'variants' => gridFeatureVariants('de fonctionnalités'),
        'preserve_fields' => true,
    ];

    $spec['cta'] = [
        'prototype' => 'cta',
        'label' => 'Appel à l\'action',
        'group' => 'cta',
        'icon' => 'fa-solid fa-bullhorn',
        'description' => 'Bandeau d\'appel à l\'action avec boutons.',
        'variants' => [
            'banner' => ['label' => 'Bandeau', 'description' => 'Bandeau pleine largeur avec boutons.'],
            'centered' => ['label' => 'Centré', 'description' => 'Texte et boutons centrés dans la section.'],
            'split' => ['label' => 'Deux colonnes', 'description' => 'Message et boutons côte à côte.'],
            'cards' => ['label' => 'Cartes', 'description' => 'Plusieurs cartes d\'action côte à côte.'],
        ],
        'preserve_fields' => true,
    ];

    $spec['pricing'] = [
        'prototype' => 'pricing',
        'label' => 'Tarifs',
        'group' => 'pricing',
        'icon' => 'fa-solid fa-tags',
        'description' => 'Cartes de tarification avec liste d\'avantages et bouton.',
        'variants' => [
            'cards' => ['label' => 'Cartes', 'description' => 'Offres présentées en cartes comparables.'],
            'compact' => ['label' => 'Compact', 'description' => 'Cartes resserrées pour comparer rapidement.'],
            'simple' => ['label' => 'Simple', 'description' => 'Mise en page épurée avec peu d\'ornements.'],
            'grid-3' => ['label' => 'Grille 3 colonnes', 'description' => 'Trois offres alignées en grille.'],
        ],
        'preserve_fields' => true,
    ];

    $spec['testimonials'] = [
        'prototype' => 'testimonials',
        'label' => 'Témoignages',
        'group' => 'testimonial',
        'icon' => 'fa-solid fa-quote-left',
        'description' => 'Citations de clients avec nom et rôle.',
        'variants' => [
            'grid' => ['label' => 'Grille', 'description' => 'Témoignages en grille responsive.'],
            'grid-2' => ['label' => 'Grille 2 colonnes', 'description' => 'Deux citations par ligne sur desktop.'],
            'masonry' => ['label' => 'Mosaïque', 'description' => 'Disposition en mosaïque de hauteurs variables.'],
            'featured' => ['label' => 'Mis en avant', 'description' => 'Un témoignage principal avec citations secondaires.'],
        ],
        'preserve_fields' => true,
    ];

    $spec['faq'] = [
        'prototype' => 'faq',
        'label' => 'FAQ',
        'group' => 'faq',
        'icon' => 'fa-solid fa-circle-question',
        'description' => 'Questions fréquentes en accordéon dépliable.',
        'variants' => [
            'list' => ['label' => 'Liste', 'description' => 'Questions empilées en liste dépliable.'],
            'two-col' => ['label' => 'Deux colonnes', 'description' => 'Questions réparties sur deux colonnes.'],
            'centered' => ['label' => 'Centré', 'description' => 'Bloc FAQ centré avec largeur limitée.'],
            'compact' => ['label' => 'Compact', 'description' => 'Espacement réduit pour une FAQ dense.'],
        ],
        'preserve_fields' => true,
    ];

    $spec['stats'] = [
        'prototype' => 'stats',
        'label' => 'Chiffres clés',
        'group' => 'stats',
        'icon' => 'fa-solid fa-chart-simple',
        'description' => 'Rangée de statistiques avec valeur et libellé.',
        'variants' => [
            'row' => ['label' => 'Rangée', 'description' => 'Chiffres alignés horizontalement.'],
            'grid-4' => ['label' => 'Grille 4 colonnes', 'description' => 'Quatre indicateurs en grille.'],
            'cards' => ['label' => 'Cartes', 'description' => 'Statistiques dans des cartes distinctes.'],
            'centered' => ['label' => 'Centré', 'description' => 'Bloc de chiffres centré sur la page.'],
        ],
        'preserve_fields' => true,
    ];

    $spec['logos'] = [
        'prototype' => 'logos',
        'label' => 'Logos / références',
        'group' => 'logos',
        'icon' => 'fa-solid fa-building',
        'description' => 'Rangée de noms de clients ou partenaires.',
        'variants' => [
            'row' => ['label' => 'Rangée', 'description' => 'Logos alignés sur une ligne.'],
            'grid' => ['label' => 'Grille', 'description' => 'Logos en grille multi-colonnes.'],
            'marquee' => ['label' => 'Défilement', 'description' => 'Bandeau de logos en défilement horizontal.'],
            'centered' => ['label' => 'Centré', 'description' => 'Bloc de logos centré avec titre.'],
        ],
        'preserve_fields' => true,
    ];

    $spec['team'] = [
        'prototype' => 'team',
        'label' => 'Équipe',
        'group' => 'team',
        'icon' => 'fa-solid fa-user-group',
        'description' => 'Membres de l\'équipe avec rôle et bio courte.',
        'variants' => [
            'grid' => ['label' => 'Grille', 'description' => 'Membres en grille responsive.'],
            'grid-2' => ['label' => 'Grille 2 colonnes', 'description' => 'Deux profils par ligne.'],
            'cards' => ['label' => 'Cartes', 'description' => 'Cartes profil avec photo et bio.'],
            'list' => ['label' => 'Liste', 'description' => 'Liste verticale de membres.'],
        ],
        'preserve_fields' => true,
    ];

    $spec['contact'] = [
        'prototype' => 'contact',
        'label' => 'Contact',
        'group' => 'contact',
        'icon' => 'fa-solid fa-envelope',
        'description' => 'Coordonnées de contact (email, téléphone, adresse).',
        'variants' => [
            'cards' => ['label' => 'Cartes', 'description' => 'Coordonnées en cartes distinctes.'],
            'split' => ['label' => 'Deux colonnes', 'description' => 'Texte d\'intro et coordonnées côte à côte.'],
            'centered' => ['label' => 'Centré', 'description' => 'Bloc de contact centré.'],
            'list' => ['label' => 'Liste', 'description' => 'Liste simple de moyens de contact.'],
        ],
        'preserve_fields' => true,
    ];

    $spec['content'] = [
        'prototype' => 'content',
        'label' => 'Texte',
        'group' => 'content',
        'icon' => 'fa-solid fa-align-left',
        'description' => 'Bloc de texte libre avec titre optionnel.',
        'variants' => [
            'prose' => ['label' => 'Colonne de texte', 'description' => 'Texte long en colonne lisible.'],
            'columns-2' => ['label' => 'Deux colonnes', 'description' => 'Texte réparti sur deux colonnes.'],
            'centered' => ['label' => 'Centré', 'description' => 'Bloc de texte centré.'],
            'quote' => ['label' => 'Citation', 'description' => 'Mise en forme type citation ou pull quote.'],
        ],
        'preserve_fields' => true,
    ];

    $spec['about'] = [
        'prototype' => 'about',
        'label' => 'À propos',
        'group' => 'about',
        'icon' => 'fa-solid fa-circle-info',
        'description' => 'Présentation en deux colonnes avec points clés.',
        'variants' => [
            'split' => ['label' => 'Texte et points', 'description' => 'Texte principal avec liste de points clés.'],
            'centered' => ['label' => 'Centré', 'description' => 'Présentation centrée avec points en dessous.'],
            'cards' => ['label' => 'Cartes', 'description' => 'Points clés en cartes.'],
            'grid-3' => ['label' => 'Grille 3 colonnes', 'description' => 'Valeurs ou piliers en trois colonnes.'],
        ],
        'preserve_fields' => true,
    ];

    $spec['steps'] = [
        'prototype' => 'steps',
        'label' => 'Étapes',
        'group' => 'process',
        'icon' => 'fa-solid fa-list-ol',
        'description' => 'Processus en plusieurs étapes numérotées.',
        'variants' => [
            'row' => ['label' => 'Rangée horizontale', 'description' => 'Étapes alignées horizontalement.'],
            'vertical' => ['label' => 'Vertical', 'description' => 'Étapes empilées verticalement.'],
            'timeline' => ['label' => 'Chronologie', 'description' => 'Ligne de temps avec étapes datées.'],
            'numbered' => ['label' => 'Numéroté', 'description' => 'Liste numérotée mise en avant.'],
        ],
        'preserve_fields' => true,
    ];

    $spec['banner'] = [
        'prototype' => 'banner',
        'label' => 'Bannière',
        'group' => 'cta',
        'icon' => 'fa-solid fa-bell',
        'description' => 'Bandeau d\'annonce en haut de section.',
        'variants' => [
            'strip' => ['label' => 'Bandeau', 'description' => 'Bandeau fin sur toute la largeur.'],
            'dismissible' => ['label' => 'Fermable', 'description' => 'Annonce avec possibilité de fermeture.'],
            'centered' => ['label' => 'Centré', 'description' => 'Message centré dans le bandeau.'],
        ],
        'preserve_fields' => true,
    ];

    $spec['compare'] = [
        'prototype' => 'compare',
        'label' => 'Comparaison',
        'group' => 'pricing',
        'icon' => 'fa-solid fa-scale-balanced',
        'description' => 'Tableau comparatif sur deux colonnes.',
        'variants' => [
            'table' => ['label' => 'Tableau', 'description' => 'Comparaison en lignes et colonnes.'],
            'cards' => ['label' => 'Cartes', 'description' => 'Deux cartes comparées côte à côte.'],
            'simple' => ['label' => 'Simple', 'description' => 'Tableau épuré sans fioritures.'],
            'compact' => ['label' => 'Compact', 'description' => 'Comparaison dense pour mobile.'],
        ],
        'preserve_fields' => true,
    ];

    $spec['gallery'] = [
        'prototype' => 'gallery',
        'label' => 'Galerie',
        'group' => 'gallery',
        'icon' => 'fa-solid fa-images',
        'description' => 'Grille d\'images avec légende.',
        'variants' => [
            'grid' => ['label' => 'Grille', 'description' => 'Images en grille responsive.'],
            'grid-2' => ['label' => 'Grille 2 colonnes', 'description' => 'Galerie en deux colonnes.'],
            'masonry' => ['label' => 'Mosaïque', 'description' => 'Disposition mosaïque d\'images.'],
            'featured' => ['label' => 'Mis en avant', 'description' => 'Une image principale avec vignettes.'],
        ],
        'preserve_fields' => true,
    ];

    $spec['newsletter'] = [
        'prototype' => 'newsletter',
        'label' => 'Inscription',
        'group' => 'newsletter',
        'icon' => 'fa-solid fa-paper-plane',
        'description' => 'Bloc d\'inscription avec bouton vers un formulaire externe.',
        'variants' => [
            'centered' => ['label' => 'Centré', 'description' => 'Formulaire centré avec titre.'],
            'split' => ['label' => 'Deux colonnes', 'description' => 'Texte et champ email côte à côte.'],
            'inline' => ['label' => 'En ligne', 'description' => 'Champ et bouton sur une seule ligne.'],
            'banner' => ['label' => 'Bandeau', 'description' => 'Inscription en bandeau pleine largeur.'],
        ],
        'preserve_fields' => true,
    ];

    $newTypes = [
        [
            'key' => 'integrations',
            'label' => 'Intégrations',
            'group' => 'integration',
            'icon' => 'fa-solid fa-plug',
            'description' => 'Connecteurs et outils compatibles avec votre produit.',
            'prototype' => 'features',
            'variants' => gridFeatureVariants('d\'intégrations'),
            'content' => contentRepeaterTitleText('Intégrations'),
        ],
        [
            'key' => 'services',
            'label' => 'Services',
            'group' => 'service',
            'icon' => 'fa-solid fa-briefcase',
            'description' => 'Offres de services avec description courte.',
            'prototype' => 'features',
            'variants' => gridFeatureVariants('de services'),
            'content' => contentRepeaterTitleText('Services'),
        ],
        [
            'key' => 'blog',
            'label' => 'Articles',
            'group' => 'blog',
            'icon' => 'fa-solid fa-newspaper',
            'description' => 'Liste d\'articles ou de billets de blog.',
            'prototype' => 'features',
            'variants' => [
                'grid-3' => ['label' => 'Grille 3 colonnes', 'description' => 'Aperçus d\'articles en trois colonnes.'],
                'grid-2' => ['label' => 'Grille 2 colonnes', 'description' => 'Articles en deux colonnes.'],
                'list' => ['label' => 'Liste', 'description' => 'Liste verticale d\'articles.'],
                'featured' => ['label' => 'Mis en avant', 'description' => 'Un article principal avec liste secondaire.'],
            ],
            'content' => contentRepeaterTitleText('Articles'),
        ],
        [
            'key' => 'timeline',
            'label' => 'Chronologie',
            'group' => 'timeline',
            'icon' => 'fa-solid fa-timeline',
            'description' => 'Événements ou jalons sur une ligne de temps.',
            'prototype' => 'steps',
            'variants' => [
                'timeline' => ['label' => 'Chronologie', 'description' => 'Ligne de temps verticale.'],
                'vertical' => ['label' => 'Vertical', 'description' => 'Jalons empilés verticalement.'],
                'row' => ['label' => 'Rangée', 'description' => 'Jalons alignés horizontalement.'],
            ],
            'content' => contentRepeaterTitleText('Jalons'),
        ],
        [
            'key' => 'signup',
            'label' => 'Inscription compte',
            'group' => 'auth',
            'icon' => 'fa-solid fa-user-plus',
            'description' => 'Bloc d\'inscription ou création de compte.',
            'prototype' => 'newsletter',
            'variants' => [
                'centered' => ['label' => 'Centré', 'description' => 'Formulaire d\'inscription centré.'],
                'split' => ['label' => 'Deux colonnes', 'description' => 'Accroche et formulaire côte à côte.'],
                'cards' => ['label' => 'Carte', 'description' => 'Formulaire dans une carte.'],
            ],
            'content' => [
                'title' => ['type' => 'text', 'label' => 'Titre', 'client_editable' => true],
                'subtitle' => ['type' => 'textarea', 'label' => 'Sous-titre', 'client_editable' => true],
                'input_hint' => ['type' => 'text', 'label' => 'Indication email', 'client_editable' => true],
                'buttons' => ['type' => 'buttons', 'label' => 'Boutons', 'client_editable' => true],
            ],
            'style' => styleFieldsStandard(),
        ],
        [
            'key' => 'login',
            'label' => 'Connexion',
            'group' => 'auth',
            'icon' => 'fa-solid fa-right-to-bracket',
            'description' => 'Bloc de connexion à un espace client.',
            'prototype' => 'newsletter',
            'variants' => [
                'centered' => ['label' => 'Centré', 'description' => 'Formulaire de connexion centré.'],
                'split' => ['label' => 'Deux colonnes', 'description' => 'Visuel et formulaire côte à côte.'],
                'minimal' => ['label' => 'Minimal', 'description' => 'Connexion compacte sans décor.'],
            ],
            'content' => [
                'title' => ['type' => 'text', 'label' => 'Titre', 'client_editable' => true],
                'subtitle' => ['type' => 'textarea', 'label' => 'Sous-titre', 'client_editable' => true],
                'input_hint' => ['type' => 'text', 'label' => 'Indication identifiant', 'client_editable' => true],
                'buttons' => ['type' => 'buttons', 'label' => 'Boutons', 'client_editable' => true],
            ],
            'style' => styleFieldsStandard(),
        ],
        [
            'key' => 'download',
            'label' => 'Téléchargement',
            'group' => 'download',
            'icon' => 'fa-solid fa-download',
            'description' => 'Invitation à télécharger une ressource ou une application.',
            'prototype' => 'cta',
            'variants' => [
                'banner' => ['label' => 'Bandeau', 'description' => 'Bandeau avec bouton de téléchargement.'],
                'centered' => ['label' => 'Centré', 'description' => 'Bloc centré avec boutons stores.'],
                'split' => ['label' => 'Deux colonnes', 'description' => 'Texte et boutons de téléchargement.'],
            ],
            'content' => [
                'title' => ['type' => 'text', 'label' => 'Titre', 'client_editable' => true],
                'subtitle' => ['type' => 'textarea', 'label' => 'Sous-titre', 'client_editable' => true],
                'buttons' => ['type' => 'buttons', 'label' => 'Boutons', 'client_editable' => true],
            ],
            'style' => styleFieldsStandard(),
        ],
        [
            'key' => 'projects',
            'label' => 'Projets',
            'group' => 'project',
            'icon' => 'fa-solid fa-folder-open',
            'description' => 'Portfolio de projets ou réalisations.',
            'prototype' => 'gallery',
            'variants' => [
                'grid' => ['label' => 'Grille', 'description' => 'Projets en grille d\'images.'],
                'grid-2' => ['label' => 'Grille 2 colonnes', 'description' => 'Deux projets par ligne.'],
                'featured' => ['label' => 'Mis en avant', 'description' => 'Projet principal et vignettes.'],
                'masonry' => ['label' => 'Mosaïque', 'description' => 'Grille mosaïque de projets.'],
            ],
            'content' => [
                'title' => ['type' => 'text', 'label' => 'Titre de section', 'client_editable' => true],
                'subtitle' => ['type' => 'textarea', 'label' => 'Sous-titre de section', 'client_editable' => true],
                'items' => [
                    'type' => 'repeater',
                    'label' => 'Projets',
                    'client_editable' => true,
                    'fields' => [
                        'url' => ['type' => 'text', 'label' => 'URL de l\'image'],
                        'title' => ['type' => 'text', 'label' => 'Titre du projet'],
                    ],
                ],
            ],
            'style' => styleFieldsStandard(),
        ],
        [
            'key' => 'case-studies',
            'label' => 'Études de cas',
            'group' => 'case-study',
            'icon' => 'fa-solid fa-file-lines',
            'description' => 'Retours clients et études de cas détaillées.',
            'prototype' => 'about',
            'variants' => [
                'split' => ['label' => 'Deux colonnes', 'description' => 'Résumé et résultats côte à côte.'],
                'cards' => ['label' => 'Cartes', 'description' => 'Études présentées en cartes.'],
                'grid-3' => ['label' => 'Grille 3 colonnes', 'description' => 'Plusieurs études en grille.'],
            ],
            'content' => contentRepeaterTitleText('Études'),
        ],
        [
            'key' => 'careers',
            'label' => 'Carrières',
            'group' => 'career',
            'icon' => 'fa-solid fa-briefcase',
            'description' => 'Offres d\'emploi et culture d\'entreprise.',
            'prototype' => 'team',
            'variants' => [
                'grid' => ['label' => 'Grille', 'description' => 'Postes en grille.'],
                'list' => ['label' => 'Liste', 'description' => 'Liste de postes ouverts.'],
                'cards' => ['label' => 'Cartes', 'description' => 'Offres en cartes.'],
            ],
            'content' => contentRepeaterTitleText('Postes'),
        ],
        [
            'key' => 'changelog',
            'label' => 'Journal des versions',
            'group' => 'changelog',
            'icon' => 'fa-solid fa-code-branch',
            'description' => 'Historique des versions et nouveautés produit.',
            'prototype' => 'faq',
            'variants' => [
                'list' => ['label' => 'Liste', 'description' => 'Entrées de changelog en liste.'],
                'timeline' => ['label' => 'Chronologie', 'description' => 'Versions sur une ligne de temps.'],
                'compact' => ['label' => 'Compact', 'description' => 'Liste dense de versions.'],
            ],
            'content' => contentRepeaterTitleText('Versions'),
        ],
        [
            'key' => 'community',
            'label' => 'Communauté',
            'group' => 'community',
            'icon' => 'fa-solid fa-users',
            'description' => 'Témoignages et preuve sociale de la communauté.',
            'prototype' => 'testimonials',
            'variants' => [
                'grid' => ['label' => 'Grille', 'description' => 'Messages communauté en grille.'],
                'grid-2' => ['label' => 'Grille 2 colonnes', 'description' => 'Deux colonnes de citations.'],
                'featured' => ['label' => 'Mis en avant', 'description' => 'Citation principale et secondaires.'],
            ],
            'content' => [
                'title' => ['type' => 'text', 'label' => 'Titre de section', 'client_editable' => true],
                'subtitle' => ['type' => 'textarea', 'label' => 'Sous-titre de section', 'client_editable' => true],
                'items' => [
                    'type' => 'repeater',
                    'label' => 'Messages',
                    'client_editable' => true,
                    'fields' => [
                        'text' => ['type' => 'textarea', 'label' => 'Citation'],
                        'title' => ['type' => 'text', 'label' => 'Nom'],
                        'role' => ['type' => 'text', 'label' => 'Rôle / communauté'],
                    ],
                ],
            ],
            'style' => styleFieldsStandard(),
        ],
        [
            'key' => 'compliance',
            'label' => 'Conformité',
            'group' => 'compliance',
            'icon' => 'fa-solid fa-shield-halved',
            'description' => 'Informations légales, RGPD ou certifications.',
            'prototype' => 'content',
            'variants' => [
                'prose' => ['label' => 'Colonne de texte', 'description' => 'Texte légal en colonne lisible.'],
                'columns-2' => ['label' => 'Deux colonnes', 'description' => 'Texte réparti sur deux colonnes.'],
                'centered' => ['label' => 'Centré', 'description' => 'Bloc centré pour un message de confiance.'],
            ],
            'content' => [
                'title' => ['type' => 'text', 'label' => 'Titre', 'client_editable' => true],
                'text' => ['type' => 'textarea', 'label' => 'Texte (un paragraphe par ligne)', 'client_editable' => true],
            ],
            'style' => styleFieldsStandard(true),
        ],
        [
            'key' => 'industries',
            'label' => 'Secteurs',
            'group' => 'industry',
            'icon' => 'fa-solid fa-industry',
            'description' => 'Secteurs d\'activité ou marchés ciblés.',
            'prototype' => 'logos',
            'variants' => [
                'row' => ['label' => 'Rangée', 'description' => 'Secteurs en rangée horizontale.'],
                'grid' => ['label' => 'Grille', 'description' => 'Secteurs en grille.'],
                'cards' => ['label' => 'Cartes', 'description' => 'Secteurs en cartes descriptives.'],
            ],
            'content' => contentRepeaterTitleText('Secteurs'),
        ],
        [
            'key' => 'highlights',
            'label' => 'Liste à puces',
            'group' => 'list',
            'icon' => 'fa-solid fa-list',
            'description' => 'Liste de points ou avantages en colonne.',
            'prototype' => 'faq',
            'variants' => [
                'list' => ['label' => 'Liste', 'description' => 'Liste verticale simple.'],
                'two-col' => ['label' => 'Deux colonnes', 'description' => 'Points sur deux colonnes.'],
                'bullets' => ['label' => 'Puces', 'description' => 'Liste à puces mise en avant.'],
            ],
            'content' => contentRepeaterTitleText('Points'),
        ],
        [
            'key' => 'experience',
            'label' => 'Expérience',
            'group' => 'experience',
            'icon' => 'fa-solid fa-star',
            'description' => 'Parcours, récompenses ou preuves d\'expérience.',
            'prototype' => 'testimonials',
            'variants' => [
                'grid' => ['label' => 'Grille', 'description' => 'Blocs d\'expérience en grille.'],
                'row' => ['label' => 'Rangée', 'description' => 'Indicateurs en rangée.'],
                'cards' => ['label' => 'Cartes', 'description' => 'Expériences en cartes.'],
            ],
            'content' => contentRepeaterTitleText('Expériences'),
        ],
        [
            'key' => 'process',
            'label' => 'Processus',
            'group' => 'process',
            'icon' => 'fa-solid fa-diagram-project',
            'description' => 'Étapes de méthode ou de livraison.',
            'prototype' => 'steps',
            'variants' => [
                'row' => ['label' => 'Rangée', 'description' => 'Étapes horizontales.'],
                'vertical' => ['label' => 'Vertical', 'description' => 'Étapes verticales.'],
                'timeline' => ['label' => 'Chronologie', 'description' => 'Processus en chronologie.'],
            ],
            'content' => contentRepeaterTitleText('Étapes'),
        ],
        [
            'key' => 'waitlist',
            'label' => 'Liste d\'attente',
            'group' => 'waitlist',
            'icon' => 'fa-solid fa-hourglass-half',
            'description' => 'Inscription à une liste d\'attente produit.',
            'prototype' => 'newsletter',
            'variants' => [
                'centered' => ['label' => 'Centré', 'description' => 'Formulaire centré.'],
                'inline' => ['label' => 'En ligne', 'description' => 'Champ email en ligne.'],
                'banner' => ['label' => 'Bandeau', 'description' => 'Bandeau d\'inscription.'],
            ],
            'content' => [
                'title' => ['type' => 'text', 'label' => 'Titre', 'client_editable' => true],
                'subtitle' => ['type' => 'textarea', 'label' => 'Sous-titre', 'client_editable' => true],
                'input_hint' => ['type' => 'text', 'label' => 'Indication email', 'client_editable' => true],
                'buttons' => ['type' => 'buttons', 'label' => 'Boutons', 'client_editable' => true],
            ],
            'style' => styleFieldsStandard(),
        ],
        [
            'key' => 'awards',
            'label' => 'Récompenses',
            'group' => 'award',
            'icon' => 'fa-solid fa-award',
            'description' => 'Prix, labels et distinctions obtenus.',
            'prototype' => 'stats',
            'variants' => [
                'row' => ['label' => 'Rangée', 'description' => 'Récompenses en rangée.'],
                'grid-4' => ['label' => 'Grille 4 colonnes', 'description' => 'Quatre distinctions en grille.'],
                'cards' => ['label' => 'Cartes', 'description' => 'Cartes de récompenses.'],
            ],
            'content' => [
                'title' => ['type' => 'text', 'label' => 'Titre de section', 'client_editable' => true],
                'items' => [
                    'type' => 'repeater',
                    'label' => 'Récompenses',
                    'client_editable' => true,
                    'fields' => [
                        'value' => ['type' => 'text', 'label' => 'Année ou note'],
                        'title' => ['type' => 'text', 'label' => 'Libellé'],
                    ],
                ],
            ],
            'style' => styleFieldsStandard(),
        ],
        [
            'key' => 'resources',
            'label' => 'Ressources',
            'group' => 'resource',
            'icon' => 'fa-solid fa-book',
            'description' => 'Guides, ebooks ou liens utiles.',
            'prototype' => 'features',
            'variants' => gridFeatureVariants('de ressources'),
            'content' => contentRepeaterTitleText('Ressources'),
        ],
        [
            'key' => 'code',
            'label' => 'Exemple de code',
            'group' => 'code',
            'icon' => 'fa-solid fa-code',
            'description' => 'Bloc de code ou snippet pour la documentation.',
            'prototype' => 'content',
            'variants' => [
                'prose' => ['label' => 'Colonne de texte', 'description' => 'Code présenté sous un titre.'],
                'centered' => ['label' => 'Centré', 'description' => 'Snippet centré.'],
                'split' => ['label' => 'Deux colonnes', 'description' => 'Explication et code côte à côte.'],
            ],
            'content' => [
                'title' => ['type' => 'text', 'label' => 'Titre', 'client_editable' => true],
                'text' => ['type' => 'textarea', 'label' => 'Code ou texte (une ligne par ligne)', 'client_editable' => true],
            ],
            'style' => styleFieldsStandard(true),
        ],
        [
            'key' => 'demo',
            'label' => 'Démo',
            'group' => 'demo',
            'icon' => 'fa-solid fa-play',
            'description' => 'Section hero orientée démo produit ou vidéo.',
            'prototype' => 'hero',
            'variants' => [
                'centered' => ['label' => 'Centré', 'description' => 'Accroche démo centrée.'],
                'split' => ['label' => 'Deux colonnes', 'description' => 'Texte et aperçu produit.'],
                'fullscreen' => ['label' => 'Plein écran', 'description' => 'Démo immersive plein écran.'],
            ],
            'content' => [
                'badge' => ['type' => 'text', 'label' => 'Badge / annonce', 'client_editable' => true],
                'title' => ['type' => 'text', 'label' => 'Titre', 'client_editable' => true],
                'subtitle' => ['type' => 'textarea', 'label' => 'Sous-titre', 'client_editable' => true],
                'image_url' => ['type' => 'text', 'label' => 'Image (URL)', 'client_editable' => true],
                'buttons' => ['type' => 'buttons', 'label' => 'Boutons', 'client_editable' => true],
            ],
            'style' => styleFieldsStandard(true),
        ],
    ];

    foreach ($newTypes as $entry) {
        $key = $entry['key'];
        $spec[$key] = [
            'prototype' => $entry['prototype'],
            'label' => $entry['label'],
            'group' => $entry['group'],
            'icon' => $entry['icon'],
            'description' => $entry['description'],
            'variants' => $entry['variants'],
            'content_fields' => $entry['content'],
            'style_fields' => $entry['style'] ?? styleFieldsStandard(),
        ];
    }

    return $spec;
}

/**
 * @param array<string, mixed> $existing
 * @param array<string, mixed> $specEntry
 * @return array<string, mixed>
 */
function mergeTypeDefinition(array $existing, array $specEntry): array
{
    $merged = $existing;
    foreach (['label', 'group', 'icon', 'description'] as $key) {
        if (isset($specEntry[$key])) {
            $merged[$key] = $specEntry[$key];
        }
    }
    if (!($specEntry['preserve_fields'] ?? false)) {
        if (isset($specEntry['content_fields'])) {
            $merged['content_fields'] = $specEntry['content_fields'];
        }
        if (isset($specEntry['style_fields'])) {
            $merged['style_fields'] = $specEntry['style_fields'];
        }
    }
    $existingVariants = is_array($merged['variants'] ?? null) ? $merged['variants'] : [];
    $specVariants = is_array($specEntry['variants'] ?? null) ? $specEntry['variants'] : [];
    foreach ($specVariants as $vKey => $vDef) {
        if (!isset($existingVariants[$vKey]) || !is_array($existingVariants[$vKey])) {
            $existingVariants[$vKey] = $vDef;
            continue;
        }
        foreach (['label', 'description'] as $f) {
            if (isset($vDef[$f])) {
                $existingVariants[$vKey][$f] = $vDef[$f];
            }
        }
    }
    $merged['variants'] = $existingVariants;

    return $merged;
}

/**
 * @param array<string, mixed> $registry
 * @return array<string, mixed>
 */
function orderRegistry(array $registry): array
{
    $byGroup = [];
    foreach ($registry as $type => $def) {
        $group = is_array($def) && is_string($def['group'] ?? null) ? $def['group'] : 'content';
        $byGroup[$group][$type] = $def;
    }
    $ordered = [];
    $seenGroups = [];
    foreach (GROUP_ORDER as $group) {
        if (!isset($byGroup[$group])) {
            continue;
        }
        ksort($byGroup[$group]);
        foreach ($byGroup[$group] as $type => $def) {
            $ordered[$type] = $def;
        }
        $seenGroups[$group] = true;
    }
    foreach ($byGroup as $group => $types) {
        if (isset($seenGroups[$group])) {
            continue;
        }
        ksort($types);
        foreach ($types as $type => $def) {
            $ordered[$type] = $def;
        }
    }

    return $ordered;
}

/** @var array<string, array{0: string, 1: string}> */
const FAMILY_SOURCE = [
    'grid-3' => ['features', 'grid-3.html'],
    'grid' => ['gallery', 'grid.html'],
    'row' => ['logos', 'row.html'],
    'cards' => ['pricing', 'cards.html'],
    'list' => ['faq', 'list.html'],
    'centered' => ['newsletter', 'centered.html'],
    'split' => ['about', 'split.html'],
    'prose' => ['content', 'prose.html'],
    'table' => ['compare', 'table.html'],
    'strip' => ['banner', 'strip.html'],
    'banner' => ['cta', 'banner.html'],
    'vertical' => ['steps', 'row.html'],
    'default' => ['features', 'grid-3.html'],
];

function adaptSectionMarkup(string $html, string $fromType, string $toType): string
{
    $from = 'section-' . $fromType;
    $to = 'section-' . $toType;

    return str_replace($from, $to, $html);
}

function adaptSectionCss(string $css, string $fromType, string $toType): string
{
    return adaptSectionMarkup($css, $fromType, $toType);
}

/**
 * @param array<string, array{label: string, description: string}> $variants
 */
function requiredHtmlFamilies(array $variants): array
{
    $families = [];
    foreach (array_keys($variants) as $variant) {
        $families[] = $variant;
        foreach (SectionLayoutFamilies::htmlFamilies((string) $variant) as $family) {
            $families[] = $family;
        }
    }
    $families[] = 'default';

    return array_values(array_unique($families));
}

function hasRepeaterItems(array $typeDef): bool
{
    $fields = $typeDef['content_fields'] ?? [];
    if (!is_array($fields)) {
        return false;
    }
    $items = $fields['items'] ?? null;

    return is_array($items) && ($items['type'] ?? '') === 'repeater';
}

/**
 * @return array{html_created: int, css_created: int}
 */
function ensureAssets(
    string $type,
    string $prototype,
    array $typeDef,
    string $sectionsDir,
    string $cssDir,
): array {
    $createdHtml = 0;
    $createdCss = 0;
    $typeDir = $sectionsDir . '/' . $type;
    $typeCssDir = $cssDir . '/' . $type;
    if (!is_dir($typeDir)) {
        mkdir($typeDir, 0775, true);
    }
    if (!is_dir($typeCssDir)) {
        mkdir($typeCssDir, 0775, true);
    }

    $variants = is_array($typeDef['variants'] ?? null) ? $typeDef['variants'] : [];
    $families = requiredHtmlFamilies($variants);

    foreach ($families as $family) {
        $targetHtml = $typeDir . '/' . $family . '.html';
        if (is_file($targetHtml)) {
            continue;
        }
        $source = resolveFamilySource($type, $prototype, $family, $sectionsDir);
        if ($source === null) {
            continue;
        }
        [$srcType, $srcFile] = $source;
        $srcPath = $sectionsDir . '/' . $srcType . '/' . $srcFile;
        if (!is_file($srcPath)) {
            continue;
        }
        $raw = file_get_contents($srcPath);
        if ($raw === false) {
            continue;
        }
        file_put_contents($targetHtml, adaptSectionMarkup($raw, $srcType, $type));
        $createdHtml++;

        $baseCssName = pathinfo($srcFile, PATHINFO_FILENAME) . '.css';
        $srcCss = $cssDir . '/' . $srcType . '/' . $baseCssName;
        $destCss = $typeCssDir . '/' . $baseCssName;
        if (is_file($srcCss) && !is_file($destCss)) {
            $cssRaw = file_get_contents($srcCss);
            if ($cssRaw !== false) {
                file_put_contents($destCss, adaptSectionCss($cssRaw, $srcType, $type));
                $createdCss++;
            }
        }
    }

    if (hasRepeaterItems($typeDef)) {
        $itemTarget = $typeDir . '/item.html';
        if (!is_file($itemTarget)) {
            $protoItem = $sectionsDir . '/' . $prototype . '/item.html';
            if (is_file($protoItem)) {
                $raw = file_get_contents($protoItem);
                if ($raw !== false) {
                    file_put_contents($itemTarget, adaptSectionMarkup($raw, $prototype, $type));
                    $createdHtml++;
                }
            }
        }
    }

    foreach (array_keys($variants) as $variant) {
        $variant = (string) $variant;
        $cssPath = $typeCssDir . '/' . $variant . '.css';
        $css = buildVariantCss($type, $variant);
        if ($css === null) {
            continue;
        }
        $existing = is_file($cssPath) ? file_get_contents($cssPath) : false;
        if ($existing === $css) {
            continue;
        }
        file_put_contents($cssPath, $css);
        $createdCss++;
    }

    return ['html_created' => $createdHtml, 'css_created' => $createdCss];
}

/**
 * @return array{0: string, 1: string}|null
 */
function resolveFamilySource(string $type, string $prototype, string $family, string $sectionsDir): ?array
{
    if ($family === $prototype || is_file($sectionsDir . '/' . $prototype . '/' . $family . '.html')) {
        return [$prototype, $family . '.html'];
    }
    if (isset(FAMILY_SOURCE[$family])) {
        return FAMILY_SOURCE[$family];
    }
    $protoPath = $sectionsDir . '/' . $prototype . '/' . $family . '.html';
    if (is_file($protoPath)) {
        return [$prototype, $family . '.html'];
    }

    return null;
}

function buildVariantCss(string $type, string $variant): ?string
{
    $root = '.section-' . $type . '--' . $variant;
    $gridSel = $root . ' .section-' . $type . '__grid';

    if (preg_match('/^grid(-\d+)?$/', $variant) === 1 || $variant === 'grid') {
        $cols = 3;
        if ($variant === 'grid-2' || $variant === 'grid') {
            $cols = 2;
        } elseif ($variant === 'grid-4') {
            $cols = 4;
        } elseif ($variant === 'grid-3') {
            $cols = 3;
        }
        $lines = [
            '@media (min-width: 768px) {',
            '    ' . $gridSel . ' {',
            '        grid-template-columns: repeat(' . $cols . ', 1fr);',
            '    }',
            '}',
            '',
        ];
        if ($variant === 'bento') {
            $lines = [
                '@media (min-width: 768px) {',
                '    ' . $gridSel . ' {',
                '        grid-template-columns: repeat(2, 1fr);',
                '        grid-template-rows: auto auto;',
                '    }',
                '    ' . $root . ' .section-' . $type . '__item:first-child {',
                '        grid-column: span 2;',
                '    }',
                '}',
                '',
            ];
        }

        return implode("\n", $lines);
    }

    if (in_array($variant, ['masonry', 'featured'], true)) {
        return $root . ' .section-' . $type . '__grid {' . "\n"
            . '    grid-auto-rows: minmax(8rem, auto);' . "\n"
            . '}' . "\n\n";
    }

    if ($variant === 'centered' || str_starts_with($variant, 'centered')) {
        return $root . ' .section-inner,' . "\n"
            . $root . ' .section-' . $type . '__inner {' . "\n"
            . '    text-align: center;' . "\n"
            . '    margin-inline: auto;' . "\n"
            . '}' . "\n\n";
    }

    if (str_starts_with($variant, 'split') || $variant === 'image-split') {
        return '@media (min-width: 768px) {' . "\n"
            . '    ' . $root . ' .section-inner,' . "\n"
            . '    ' . $root . ' .section-' . $type . '__inner {' . "\n"
            . '        display: grid;' . "\n"
            . '        grid-template-columns: 1fr 1fr;' . "\n"
            . '        gap: var(--space-lg);' . "\n"
            . '        align-items: center;' . "\n"
            . '    }' . "\n"
            . '}' . "\n\n";
    }

    if ($variant === 'compact' || $variant === 'minimal') {
        return $root . ' {' . "\n"
            . '    --section-density: compact;' . "\n"
            . '}' . "\n\n";
    }

    if ($variant === 'inline') {
        return $root . ' .section-buttons,' . "\n"
            . $root . ' form {' . "\n"
            . '    display: flex;' . "\n"
            . '    flex-wrap: wrap;' . "\n"
            . '    gap: var(--space-sm);' . "\n"
            . '    justify-content: center;' . "\n"
            . '}' . "\n\n";
    }

    if ($variant === 'vertical' || $variant === 'timeline') {
        return $root . ' .section-' . $type . '__grid,' . "\n"
            . $root . ' .section-' . $type . '__row {' . "\n"
            . '    flex-direction: column;' . "\n"
            . '    display: flex;' . "\n"
            . '    gap: var(--space-md);' . "\n"
            . '}' . "\n\n";
    }

    if ($variant === 'marquee') {
        return $root . ' .section-' . $type . '__grid {' . "\n"
            . '    display: flex;' . "\n"
            . '    flex-wrap: nowrap;' . "\n"
            . '    overflow-x: auto;' . "\n"
            . '    gap: var(--space-lg);' . "\n"
            . '}' . "\n\n";
    }

    if ($variant === 'two-col') {
        return '@media (min-width: 768px) {' . "\n"
            . '    ' . $gridSel . ' {' . "\n"
            . '        grid-template-columns: repeat(2, 1fr);' . "\n"
            . '    }' . "\n"
            . '}' . "\n\n";
    }

    if ($variant === 'columns-2' || $variant === 'quote' || $variant === 'fullscreen' || $variant === 'video' || $variant === 'dismissible') {
        return $root . ' {' . "\n"
            . '    /* variante ' . $variant . ' */' . "\n"
            . '}' . "\n\n";
    }

    return null;
}

/**
 * @param array<string, mixed> $registry
 */
function countPickerCards(array $registry): int
{
    $count = 0;
    foreach ($registry as $type => $def) {
        if (!is_array($def)) {
            continue;
        }
        $variants = is_array($def['variants'] ?? null) ? $def['variants'] : [];
        if (count($variants) > 1) {
            $count += count($variants);
        } else {
            $count += 1;
        }
    }

    return $count;
}

// --- Main ---

$existing = is_file($registryPath) ? YamlData::parse((string) file_get_contents($registryPath)) : [];
$spec = buildCatalogSpec();
$merged = [];

foreach ($spec as $type => $specEntry) {
    $merged[$type] = mergeTypeDefinition(is_array($existing[$type] ?? null) ? $existing[$type] : [], $specEntry);
}

$registry = orderRegistry($merged);
file_put_contents($registryPath, CatalogYamlWriter::dump($registry));

$htmlCreated = 0;
$cssCreated = 0;
$variantTotal = 0;

foreach ($registry as $type => $def) {
    if (!is_array($def)) {
        continue;
    }
    $prototype = is_string($spec[$type]['prototype'] ?? null) ? $spec[$type]['prototype'] : $type;
    $variants = is_array($def['variants'] ?? null) ? $def['variants'] : [];
    $variantTotal += count($variants);
    $result = ensureAssets($type, $prototype, $def, $sectionsDir, $cssDir);
    $htmlCreated += $result['html_created'];
    $cssCreated += $result['css_created'];
}

$typeCount = count($registry);
$pickerCards = countPickerCards($registry);

echo "Catalogue sections généré.\n";
echo "Types: {$typeCount}\n";
echo "Variantes: {$variantTotal}\n";
echo "Cartes picker: {$pickerCards}\n";
echo "Fichiers HTML créés: {$htmlCreated}\n";
echo "Fichiers CSS créés/mis à jour: {$cssCreated}\n";
