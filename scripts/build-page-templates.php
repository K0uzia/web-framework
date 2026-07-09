<?php

declare(strict_types=1);

/**
 * Génère resources/page-templates/registry.yaml (49 modèles + page vide).
 *
 * Usage: php scripts/build-page-templates.php
 */

require dirname(__DIR__) . '/vendor/autoload.php';


$root = dirname(__DIR__);
$registryPath = $root . '/resources/page-templates/registry.yaml';
$sectionsRegistryPath = $root . '/resources/sections/registry.yaml';

final class PageTemplateYamlWriter
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
            if ($key === 'sections') {
                $lines[] = $pad . $key . ': []';

                return;
            }
            $lines[] = $pad . $key . ': []';

            return;
        }
        if (self::isList($value)) {
            if (self::isListOfMaps($value)) {
                $lines[] = $pad . $key . ':';
                foreach ($value as $item) {
                    self::dumpSequenceItem($lines, $item, $indent + 2);
                }

                return;
            }
            $lines[] = $pad . $key . ': ' . self::inlineList($value);

            return;
        }
        $lines[] = $pad . $key . ':';
        foreach ($value as $childKey => $childValue) {
            self::dumpKey($lines, (string) $childKey, $childValue, $indent + 2);
        }
    }

    /**
     * @param list<string> $lines
     * @param array<string, mixed> $item
     */
    private static function dumpSequenceItem(array &$lines, array $item, int $indent): void
    {
        $pad = str_repeat(' ', $indent);
        $first = true;
        foreach ($item as $childKey => $childValue) {
            if ($first) {
                if (is_array($childValue) && !self::isList($childValue)) {
                    $lines[] = $pad . '- ' . $childKey . ':';
                    foreach ($childValue as $gk => $gv) {
                        self::dumpKey($lines, (string) $gk, $gv, $indent + 4);
                    }
                } else {
                    $lines[] = $pad . '- ' . $childKey . ': ' . self::scalar($childValue);
                }
                $first = false;
                continue;
            }
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
     * @param array<int, mixed> $list
     */
    private static function isListOfMaps(array $list): bool
    {
        foreach ($list as $item) {
            if (!is_array($item) || self::isList($item)) {
                return false;
            }
        }

        return true;
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
 * @return array<string, list<string>>
 */
function loadSectionVariants(string $path): array
{
    $raw = file_get_contents($path);
    if ($raw === false) {
        throw new RuntimeException('Cannot read sections registry: ' . $path);
    }
    preg_match_all('/^([a-z][a-z0-9-]*):\n((?:  .*\n)*)/m', $raw, $blocks, PREG_SET_ORDER);
    $out = [];
    foreach ($blocks as $block) {
        $type = $block[1];
        if (preg_match('/variants:\n((?:    [a-z0-9-]+:\n(?:      .*\n)*)*)/', $block[2], $v)) {
            preg_match_all('/^    ([a-z0-9-]+):/m', $v[1], $vars);
            $out[$type] = $vars[1];
        } else {
            $out[$type] = [];
        }
    }

    return $out;
}

/**
 * @param list<array<string, mixed>> $sections
 */
function validateSections(array $sections, array $sectionVariants): void
{
    foreach ($sections as $section) {
        $type = $section['type'] ?? '';
        $variant = $section['variant'] ?? '';
        if ($type === '' || !array_key_exists($type, $sectionVariants)) {
            throw new RuntimeException('Unknown section type: ' . $type);
        }
        $variants = $sectionVariants[$type];
        if ($variants !== [] && !in_array($variant, $variants, true)) {
            throw new RuntimeException("Unknown variant {$variant} for section {$type}");
        }
    }
}

/**
 * @param list<array<string, mixed>> $sections
 * @return array<string, mixed>
 */
function template(
    string $category,
    string $label,
    string $description,
    string $icon,
    string $slug,
    string $title,
    string $seo,
    bool $publish,
    array $sections
): array {
    return [
        'category' => $category,
        'label' => $label,
        'description' => $description,
        'icon' => $icon,
        'slug' => $slug,
        'title' => $title,
        'seo' => $seo,
        'publish' => $publish,
        'sections' => $sections,
    ];
}

/**
 * @return array<string, mixed>
 */
function sec(string $type, string $variant, ?array $content = null): array
{
    $row = ['type' => $type, 'variant' => $variant];
    if ($content !== null && $content !== []) {
        $row['content'] = $content;
    }

    return $row;
}

$sectionVariants = loadSectionVariants($sectionsRegistryPath);

/** @var array<string, array<string, mixed>> $registry */
$registry = [];

// landing (20)
$registry['landing-accueil'] = template(
    'landing',
    'Page d\'accueil classique',
    'Hero centré, logos, fonctionnalités, étapes, témoignages, newsletter et CTA.',
    'fa-solid fa-rocket',
    '',
    'Accueil',
    'Découvrez notre produit et commencez en quelques minutes.',
    true,
    [
        sec('hero', 'centered', ['title' => 'Bienvenue', 'subtitle' => 'La solution simple pour avancer plus vite.']),
        sec('logos', 'row'),
        sec('features', 'grid-3'),
        sec('steps', 'row'),
        sec('testimonials', 'grid'),
        sec('newsletter', 'inline'),
        sec('cta', 'banner'),
    ]
);

$registry['landing-saas'] = template(
    'landing',
    'Landing SaaS',
    'Hero deux colonnes, bento, intégrations, tarifs et FAQ.',
    'fa-solid fa-cloud',
    'saas',
    'Produit SaaS',
    'Logiciel en ligne pour les équipes modernes.',
    true,
    [
        sec('hero', 'split'),
        sec('logos', 'row'),
        sec('features', 'bento'),
        sec('integrations', 'grid-3'),
        sec('pricing', 'cards'),
        sec('faq', 'list'),
        sec('cta', 'banner'),
    ]
);

$registry['landing-startup'] = template(
    'landing',
    'Landing startup',
    'Hero plein écran, chiffres, fonctionnalités et preuve sociale.',
    'fa-solid fa-lightbulb',
    'startup',
    'Lancez votre projet',
    'Une landing percutante pour une jeune entreprise.',
    true,
    [
        sec('hero', 'fullscreen'),
        sec('stats', 'grid-4'),
        sec('features', 'grid-3'),
        sec('testimonials', 'featured'),
        sec('cta', 'centered'),
    ]
);

$registry['landing-agence'] = template(
    'landing',
    'Landing agence',
    'Hero visuel, services, galerie et témoignages.',
    'fa-solid fa-palette',
    'agence',
    'Agence créative',
    'Design, stratégie et réalisations sur mesure.',
    true,
    [
        sec('hero', 'image-below'),
        sec('services', 'grid-3'),
        sec('gallery', 'grid'),
        sec('testimonials', 'grid'),
        sec('cta', 'split'),
    ]
);

$registry['landing-application'] = template(
    'landing',
    'Landing application',
    'Hero inversé, points clés, démo et conversion.',
    'fa-solid fa-mobile-screen',
    'application',
    'Notre application',
    'Téléchargez l\'app et travaillez où vous voulez.',
    true,
    [
        sec('hero', 'split-left'),
        sec('features', 'grid-2'),
        sec('stats', 'cards'),
        sec('demo', 'centered'),
        sec('cta', 'banner'),
    ]
);

$registry['landing-produit'] = template(
    'landing',
    'Landing produit',
    'Badge, points forts, études de cas et CTA.',
    'fa-solid fa-box-open',
    'produit',
    'Découvrir le produit',
    'Vue d\'ensemble des bénéfices et des cas clients.',
    true,
    [
        sec('hero', 'badge', ['badge' => 'Nouveau', 'title' => 'Le produit qui change la donne']),
        sec('highlights', 'two-col'),
        sec('features', 'bento'),
        sec('case-studies', 'cards'),
        sec('cta', 'banner'),
    ]
);

$registry['landing-mobile'] = template(
    'landing',
    'Landing mobile',
    'Accroche centrée, liste de fonctions, téléchargement.',
    'fa-solid fa-mobile',
    'mobile',
    'Application mobile',
    'Une expérience fluide sur iOS et Android.',
    true,
    [
        sec('hero', 'centered'),
        sec('features', 'list'),
        sec('stats', 'row'),
        sec('testimonials', 'grid-2'),
        sec('download', 'banner'),
        sec('cta', 'centered'),
    ]
);

$registry['landing-api'] = template(
    'landing',
    'Landing API',
    'Hero minimal, extrait de code et intégrations.',
    'fa-solid fa-code',
    'api',
    'API développeurs',
    'Intégrez notre API en quelques lignes de code.',
    true,
    [
        sec('hero', 'minimal'),
        sec('code', 'split'),
        sec('features', 'grid-3'),
        sec('integrations', 'list'),
        sec('cta', 'banner'),
    ]
);

$registry['landing-open-source'] = template(
    'landing',
    'Landing open source',
    'Communauté, fonctionnalités et équipe contributrice.',
    'fa-solid fa-code-branch',
    'open-source',
    'Projet open source',
    'Contribuez, forkez et construisons ensemble.',
    true,
    [
        sec('hero', 'centered'),
        sec('community', 'grid'),
        sec('features', 'grid-3'),
        sec('team', 'grid'),
        sec('cta', 'centered'),
    ]
);

$registry['landing-evenement'] = template(
    'landing',
    'Landing événement',
    'Badge, programme et intervenants.',
    'fa-solid fa-calendar-days',
    'evenement',
    'Événement à venir',
    'Inscrivez-vous à notre prochain rendez-vous.',
    true,
    [
        sec('hero', 'badge', ['badge' => 'Événement', 'title' => 'Rejoignez-nous en direct']),
        sec('timeline', 'row'),
        sec('team', 'cards'),
        sec('cta', 'banner'),
    ]
);

$registry['landing-lancement'] = template(
    'landing',
    'Landing lancement',
    'Vidéo hero, liste d\'attente et logos.',
    'fa-solid fa-bullhorn',
    'lancement',
    'Bientôt disponible',
    'Soyez parmi les premiers informés du lancement.',
    true,
    [
        sec('hero', 'video'),
        sec('waitlist', 'centered'),
        sec('logos', 'marquee'),
        sec('features', 'grid-3'),
        sec('cta', 'banner'),
    ]
);

$registry['landing-entreprise'] = template(
    'landing',
    'Landing entreprise',
    'Hero split, secteurs, conformité et preuves.',
    'fa-solid fa-building',
    'entreprise',
    'Solutions entreprise',
    'Sécurité, échelle et accompagnement dédié.',
    true,
    [
        sec('hero', 'split'),
        sec('industries', 'cards'),
        sec('compliance', 'prose'),
        sec('stats', 'row'),
        sec('logos', 'row'),
        sec('cta', 'banner'),
    ]
);

$registry['landing-creatif'] = template(
    'landing',
    'Landing portfolio créatif',
    'Plein écran, galerie, projets et contact.',
    'fa-solid fa-images',
    'portfolio-creatif',
    'Portfolio',
    'Réalisations récentes et projets phares.',
    true,
    [
        sec('hero', 'fullscreen'),
        sec('gallery', 'masonry'),
        sec('projects', 'featured'),
        sec('testimonials', 'featured'),
        sec('contact', 'cards'),
    ]
);

$registry['landing-education'] = template(
    'landing',
    'Landing éducation',
    'Processus pédagogique, ressources et conversion.',
    'fa-solid fa-graduation-cap',
    'education',
    'Formation et ressources',
    'Apprenez à votre rythme avec nos guides.',
    true,
    [
        sec('hero', 'centered'),
        sec('process', 'vertical'),
        sec('features', 'grid-3'),
        sec('resources', 'grid-2'),
        sec('cta', 'banner'),
    ]
);

$registry['landing-sante'] = template(
    'landing',
    'Landing santé',
    'Message rassurant, conformité et fonctionnalités.',
    'fa-solid fa-heart-pulse',
    'sante',
    'Santé et bien-être',
    'Des outils fiables pour les professionnels de santé.',
    true,
    [
        sec('hero', 'split'),
        sec('stats', 'centered'),
        sec('features', 'grid-3'),
        sec('compliance', 'centered'),
        sec('cta', 'banner'),
    ]
);

$registry['landing-finance'] = template(
    'landing',
    'Landing finance',
    'Hero épuré, chiffres denses et témoignages.',
    'fa-solid fa-chart-pie',
    'finance',
    'Services financiers',
    'Clarté, performance et transparence.',
    true,
    [
        sec('hero', 'minimal'),
        sec('stats', 'grid-4'),
        sec('features', 'grid-4'),
        sec('testimonials', 'grid-2'),
        sec('cta', 'banner'),
    ]
);

$registry['landing-ecommerce'] = template(
    'landing',
    'Landing e-commerce',
    'Visuel sous le titre, tarifs et avis clients.',
    'fa-solid fa-cart-shopping',
    'ecommerce',
    'Boutique en ligne',
    'Vendez plus avec une vitrine optimisée.',
    true,
    [
        sec('hero', 'image-below'),
        sec('features', 'bento'),
        sec('testimonials', 'grid'),
        sec('pricing', 'compact'),
        sec('cta', 'centered'),
    ]
);

$registry['landing-conference'] = template(
    'landing',
    'Landing conférence',
    'Intervenants, agenda et sponsors.',
    'fa-solid fa-microphone',
    'conference',
    'Conférence annuelle',
    'Programme, speakers et billetterie.',
    true,
    [
        sec('hero', 'badge', ['badge' => 'Conférence', 'title' => 'Rencontrez les experts']),
        sec('team', 'grid'),
        sec('timeline', 'vertical'),
        sec('logos', 'grid'),
        sec('cta', 'banner'),
    ]
);

$registry['landing-partenaires'] = template(
    'landing',
    'Landing partenaires',
    'Logos défilants, études de cas et stats.',
    'fa-solid fa-handshake',
    'partenaires',
    'Nos partenaires',
    'Un écosystème de confiance autour du produit.',
    true,
    [
        sec('hero', 'centered'),
        sec('logos', 'marquee'),
        sec('case-studies', 'grid-3'),
        sec('stats', 'row'),
        sec('cta', 'split'),
    ]
);

$registry['landing-beta'] = template(
    'landing',
    'Landing bêta',
    'Liste d\'attente, FAQ courte et preuves.',
    'fa-solid fa-flask',
    'beta',
    'Programme bêta',
    'Testez les prochaines fonctionnalités en avant-première.',
    true,
    [
        sec('hero', 'minimal'),
        sec('waitlist', 'inline'),
        sec('features', 'grid-2'),
        sec('faq', 'compact'),
        sec('logos', 'row'),
    ]
);

// blog (5)
$registry['blog-index'] = template(
    'blog',
    'Blog, liste',
    'Hero, grille d\'articles, newsletter et CTA.',
    'fa-solid fa-newspaper',
    'blog',
    'Blog',
    'Articles, guides et actualités.',
    true,
    [
        sec('hero', 'centered'),
        sec('blog', 'grid-3'),
        sec('newsletter', 'inline'),
        sec('cta', 'centered'),
    ]
);

$registry['blog-magazine'] = template(
    'blog',
    'Blog magazine',
    'Article à la une et flux secondaire.',
    'fa-solid fa-book-open',
    'magazine',
    'Magazine',
    'Longs formats et dossiers thématiques.',
    true,
    [
        sec('hero', 'split'),
        sec('blog', 'featured'),
        sec('blog', 'list'),
        sec('newsletter', 'split'),
    ]
);

$registry['blog-article'] = template(
    'blog',
    'Article de blog',
    'Hero minimal, corps de texte et CTA de fin.',
    'fa-solid fa-file-lines',
    'article-exemple',
    'Titre de l\'article',
    'Résumé court de l\'article pour les moteurs de recherche.',
    true,
    [
        sec('hero', 'minimal', ['title' => 'Titre de l\'article']),
        sec('content', 'prose'),
        sec('cta', 'centered', ['title' => 'Envie d\'aller plus loin ?']),
    ]
);

$registry['blog-auteur'] = template(
    'blog',
    'Page auteur',
    'Présentation de l\'auteur et ses articles.',
    'fa-solid fa-user-pen',
    'auteur',
    'À propos de l\'auteur',
    'Biographie et publications récentes.',
    true,
    [
        sec('hero', 'centered'),
        sec('content', 'columns-2'),
        sec('blog', 'list'),
        sec('cta', 'banner'),
    ]
);

$registry['blog-categories'] = template(
    'blog',
    'Blog par catégories',
    'Badge, grille thématique et ressources.',
    'fa-solid fa-tags',
    'categories-blog',
    'Catégories',
    'Parcourez les articles par thème.',
    true,
    [
        sec('hero', 'badge', ['badge' => 'Thèmes', 'title' => 'Explorez par catégorie']),
        sec('blog', 'grid-2'),
        sec('resources', 'list'),
        sec('newsletter', 'banner'),
    ]
);

// pricing (5)
$registry['pricing-standard'] = template(
    'pricing',
    'Tarifs standard',
    'Grille tarifaire, comparaison et FAQ.',
    'fa-solid fa-tags',
    'tarifs',
    'Tarifs',
    'Des formules simples et transparentes pour chaque besoin.',
    true,
    [
        sec('hero', 'centered'),
        sec('pricing', 'cards'),
        sec('compare', 'table'),
        sec('faq', 'list'),
        sec('cta', 'banner'),
    ]
);

$registry['pricing-saas'] = template(
    'pricing',
    'Tarifs SaaS',
    'Plans en grille, comparatif et témoignages.',
    'fa-solid fa-layer-group',
    'tarifs-saas',
    'Abonnements',
    'Choisissez le plan adapté à votre équipe.',
    true,
    [
        sec('hero', 'split'),
        sec('pricing', 'grid-3'),
        sec('compare', 'cards'),
        sec('testimonials', 'grid'),
        sec('cta', 'centered'),
    ]
);

$registry['pricing-compare'] = template(
    'pricing',
    'Comparatif de formules',
    'Tableau comparatif et tarifs compacts.',
    'fa-solid fa-scale-balanced',
    'comparatif-tarifs',
    'Comparer les offres',
    'Toutes les options côte à côte.',
    true,
    [
        sec('hero', 'minimal'),
        sec('compare', 'simple'),
        sec('pricing', 'compact'),
        sec('faq', 'two-col'),
        sec('cta', 'banner'),
    ]
);

$registry['pricing-enterprise'] = template(
    'pricing',
    'Tarifs entreprise',
    'Offre sur mesure, fonctionnalités et contact.',
    'fa-solid fa-building-columns',
    'tarifs-entreprise',
    'Entreprise',
    'Devis personnalisé et accompagnement dédié.',
    true,
    [
        sec('hero', 'badge', ['badge' => 'Sur mesure', 'title' => 'Parlons de votre besoin']),
        sec('pricing', 'simple'),
        sec('features', 'grid-3'),
        sec('contact', 'split'),
        sec('cta', 'banner'),
    ]
);

$registry['pricing-simple'] = template(
    'pricing',
    'Tarifs simples',
    'Peu de formules, FAQ courte.',
    'fa-solid fa-coins',
    'prix',
    'Nos prix',
    'Une offre claire, sans surprise.',
    true,
    [
        sec('hero', 'centered'),
        sec('pricing', 'compact'),
        sec('faq', 'compact'),
        sec('cta', 'centered'),
    ]
);

// changelog (4)
$registry['changelog-liste'] = template(
    'changelog',
    'Changelog liste',
    'Historique des versions en liste.',
    'fa-solid fa-clock-rotate-left',
    'changelog',
    'Journal des versions',
    'Nouveautés et corrections par version.',
    true,
    [
        sec('hero', 'centered'),
        sec('changelog', 'list'),
    ]
);

$registry['changelog-timeline'] = template(
    'changelog',
    'Changelog frise',
    'Versions sur une frise chronologique.',
    'fa-solid fa-timeline',
    'changelog-frise',
    'Évolution du produit',
    'Les jalons majeurs version après version.',
    true,
    [
        sec('hero', 'minimal'),
        sec('changelog', 'timeline'),
        sec('newsletter', 'inline'),
    ]
);

$registry['changelog-compact'] = template(
    'changelog',
    'Changelog compact',
    'Notes de version denses et CTA.',
    'fa-solid fa-list',
    'notes-version',
    'Notes de version',
    'Résumé rapide des dernières mises à jour.',
    true,
    [
        sec('hero', 'centered'),
        sec('changelog', 'compact'),
        sec('cta', 'centered'),
    ]
);

$registry['changelog-roadmap'] = template(
    'changelog',
    'Roadmap produit',
    'Feuille de route et journal.',
    'fa-solid fa-road',
    'roadmap',
    'Feuille de route',
    'Ce qui arrive et ce qui vient de sortir.',
    true,
    [
        sec('hero', 'split'),
        sec('timeline', 'vertical'),
        sec('changelog', 'list'),
        sec('cta', 'banner'),
    ]
);

// about (3)
$registry['about-equipe'] = template(
    'about',
    'À propos et équipe',
    'Mission, chiffres et grille équipe.',
    'fa-solid fa-user-group',
    'equipe',
    'Notre équipe',
    'Les personnes derrière le produit.',
    true,
    [
        sec('hero', 'centered', ['title' => 'À propos de nous']),
        sec('about', 'split'),
        sec('stats', 'row'),
        sec('team', 'grid'),
        sec('cta', 'banner'),
    ]
);

$registry['about-mission'] = template(
    'about',
    'Mission et valeurs',
    'Présentation centrée et frise.',
    'fa-solid fa-compass',
    'mission',
    'Notre mission',
    'Ce qui nous guide au quotidien.',
    true,
    [
        sec('hero', 'split'),
        sec('about', 'centered'),
        sec('stats', 'cards'),
        sec('timeline', 'timeline'),
        sec('cta', 'centered'),
    ]
);

$registry['about-histoire'] = template(
    'about',
    'Notre histoire',
    'Récit visuel et équipe en cartes.',
    'fa-solid fa-landmark',
    'notre-histoire',
    'Notre histoire',
    'Les étapes clés de notre parcours.',
    true,
    [
        sec('hero', 'image-below'),
        sec('about', 'cards'),
        sec('timeline', 'vertical'),
        sec('team', 'grid-2'),
        sec('cta', 'banner'),
    ]
);

// contact (3)
$registry['contact-formulaire'] = template(
    'contact',
    'Contact formulaire',
    'Accroche, coordonnées et FAQ.',
    'fa-solid fa-envelope',
    'contact',
    'Contact',
    'Contactez-nous, nous répondons sous 24 h ouvrées.',
    true,
    [
        sec('hero', 'centered'),
        sec('contact', 'cards'),
        sec('faq', 'list'),
    ]
);

$registry['contact-bureaux'] = template(
    'contact',
    'Contact bureaux',
    'Colonnes contact et texte d\'information.',
    'fa-solid fa-location-dot',
    'bureaux',
    'Nos bureaux',
    'Adresses et horaires de nos sites.',
    true,
    [
        sec('hero', 'split'),
        sec('contact', 'split'),
        sec('stats', 'row'),
        sec('content', 'prose'),
    ]
);

$registry['contact-support'] = template(
    'contact',
    'Support client',
    'FAQ support et formulaire liste.',
    'fa-solid fa-headset',
    'support',
    'Support',
    'Aide rapide pour vos questions techniques.',
    true,
    [
        sec('hero', 'minimal'),
        sec('contact', 'list'),
        sec('faq', 'two-col'),
        sec('cta', 'banner'),
    ]
);

// integrations (3)
$registry['integrations-catalogue'] = template(
    'integrations',
    'Catalogue intégrations',
    'Grille d\'outils connectés et logos.',
    'fa-solid fa-plug',
    'integrations',
    'Intégrations',
    'Connectez votre stack en quelques clics.',
    true,
    [
        sec('hero', 'centered'),
        sec('integrations', 'grid-3'),
        sec('logos', 'row'),
        sec('cta', 'banner'),
    ]
);

$registry['integrations-partenaires'] = template(
    'integrations',
    'Intégrations partenaires',
    'Bento, témoignages et études de cas.',
    'fa-solid fa-puzzle-piece',
    'integrations-partenaires',
    'Écosystème partenaires',
    'Des connecteurs validés par nos partenaires.',
    true,
    [
        sec('hero', 'badge', ['badge' => 'Partenaires', 'title' => 'Intégrations certifiées']),
        sec('integrations', 'bento'),
        sec('testimonials', 'grid'),
        sec('case-studies', 'split'),
    ]
);

$registry['integrations-developpeurs'] = template(
    'integrations',
    'Intégrations développeurs',
    'Code, liste technique et ressources.',
    'fa-solid fa-terminal',
    'integrations-dev',
    'Pour les développeurs',
    'Documentation, SDK et exemples de code.',
    true,
    [
        sec('hero', 'split'),
        sec('code', 'centered'),
        sec('integrations', 'list'),
        sec('resources', 'grid-3'),
        sec('cta', 'banner'),
    ]
);

// faq (2)
$registry['faq-generale'] = template(
    'faq',
    'FAQ générale',
    'Accordéon de questions et CTA.',
    'fa-solid fa-circle-question',
    'faq',
    'FAQ',
    'Réponses aux questions les plus fréquentes.',
    true,
    [
        sec('hero', 'centered'),
        sec('faq', 'list'),
        sec('cta', 'banner', ['title' => 'Une autre question ?']),
    ]
);

$registry['faq-produit'] = template(
    'faq',
    'FAQ produit',
    'Deux colonnes et contact rapide.',
    'fa-solid fa-circle-info',
    'faq-produit',
    'FAQ produit',
    'Tout savoir sur l\'utilisation du produit.',
    true,
    [
        sec('hero', 'minimal'),
        sec('faq', 'two-col'),
        sec('contact', 'centered'),
        sec('cta', 'centered'),
    ]
);

// product (2)
$registry['product-overview'] = template(
    'product',
    'Vue produit',
    'Présentation, stats et démo.',
    'fa-solid fa-cube',
    'produit-apercu',
    'Vue d\'ensemble',
    'Comprenez la valeur du produit en un coup d\'œil.',
    true,
    [
        sec('hero', 'split'),
        sec('features', 'grid-3', ['title' => 'Ce que vous gagnez']),
        sec('stats', 'row'),
        sec('demo', 'centered'),
        sec('cta', 'banner'),
    ]
);

$registry['product-detail'] = template(
    'product',
    'Fiche produit',
    'Hero immersif, comparatif et preuves.',
    'fa-solid fa-box',
    'fiche-produit',
    'Fiche produit',
    'Détails, différenciation et retours clients.',
    true,
    [
        sec('hero', 'fullscreen'),
        sec('highlights', 'bullets'),
        sec('features', 'bento'),
        sec('compare', 'table'),
        sec('testimonials', 'featured'),
        sec('cta', 'cards'),
    ]
);

// feature (2)
$registry['feature-grille'] = template(
    'feature',
    'Fonctionnalités grille',
    'Hero split et grille de points clés.',
    'fa-solid fa-table-cells-large',
    'fonctionnalites',
    'Fonctionnalités',
    'Découvrez les points clés du produit.',
    true,
    [
        sec('hero', 'split'),
        sec('features', 'grid-3'),
        sec('stats', 'row'),
        sec('cta', 'banner'),
    ]
);

$registry['feature-deep-dive'] = template(
    'feature',
    'Fonctionnalité détaillée',
    'Badge, processus et étapes numérotées.',
    'fa-solid fa-magnifying-glass-chart',
    'fonctionnalite-detail',
    'Fonction en détail',
    'Plongée dans une capacité clé du produit.',
    true,
    [
        sec('hero', 'badge', ['badge' => 'Focus', 'title' => 'Une fonctionnalité essentielle']),
        sec('features', 'list'),
        sec('process', 'row'),
        sec('steps', 'numbered'),
        sec('cta', 'centered'),
    ]
);

$registry['blank'] = template(
    'blank',
    'Page vide',
    'Aucun bloc, vous composez la page vous-même.',
    'fa-solid fa-file',
    '',
    '',
    '',
    false,
    []
);

$expectedCounts = [
    'landing' => 20,
    'blog' => 5,
    'pricing' => 5,
    'changelog' => 4,
    'about' => 3,
    'contact' => 3,
    'integrations' => 3,
    'faq' => 2,
    'product' => 2,
    'feature' => 2,
];

$actualCounts = [];
foreach ($registry as $id => $def) {
    if ($id === 'blank') {
        continue;
    }
    $cat = $def['category'];
    $actualCounts[$cat] = ($actualCounts[$cat] ?? 0) + 1;
    validateSections($def['sections'], $sectionVariants);
}

foreach ($expectedCounts as $cat => $expected) {
    $got = $actualCounts[$cat] ?? 0;
    if ($got !== $expected) {
        fwrite(STDERR, "Category {$cat}: expected {$expected}, got {$got}\n");
        exit(1);
    }
}

$slugs = [];
foreach ($registry as $id => $def) {
    if ($id === 'blank') {
        continue;
    }
    $slug = $def['slug'];
    if ($slug === '') {
        continue;
    }
    if (isset($slugs[$slug])) {
        fwrite(STDERR, "Duplicate slug: {$slug}\n");
        exit(1);
    }
    $slugs[$slug] = $id;
}

if (!is_dir(dirname($registryPath))) {
    mkdir(dirname($registryPath), 0755, true);
}

file_put_contents($registryPath, PageTemplateYamlWriter::dump($registry));

$count = count($registry);
echo "Modèles de pages générés : {$count}\n";
