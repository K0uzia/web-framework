<?php

declare(strict_types=1);

namespace App\Http\Dev;

/**
 * Catalogue des 49 modèles de page (aligné sur shadcnblocks Pages).
 *
 * @see https://www.shadcnblocks.com/pages
 */
final class PageTemplatesCatalog
{
    /** @var list<array{id: string, category: string, label: string, description: string, icon: string, slug: string, title: string, seo: string, publish: bool, sections: list<array{type: string, variant: string}>}>|null */
    private static ?array $cache = null;

    /**
     * @return list<array{id: string, category: string, label: string, description: string, icon: string, slug: string, title: string, seo: string, publish: bool}>
     */
    public static function definitions(): array
    {
        return array_map(static fn (array $t): array => [
            'id' => $t['id'],
            'category' => $t['category'],
            'label' => $t['label'],
            'description' => $t['description'],
            'icon' => $t['icon'],
            'slug' => $t['slug'],
            'title' => $t['title'],
            'seo' => $t['seo'],
            'publish' => $t['publish'],
        ], self::all());
    }

    /**
     * @return list<string>
     */
    public static function categories(): array
    {
        $cats = [];
        foreach (self::all() as $t) {
            $cats[$t['category']] = true;
        }

        $order = ['blank', 'landing', 'blog', 'pricing', 'changelog', 'about', 'contact', 'integrations', 'faq', 'product', 'feature'];
        $result = [];
        foreach ($order as $cat) {
            if (isset($cats[$cat])) {
                $result[] = $cat;
            }
        }

        return $result;
    }

    /**
     * @return list<array{type: string, variant: string}>
     */
    public static function sectionRefs(string $templateId): array
    {
        foreach (self::all() as $t) {
            if ($t['id'] === $templateId) {
                return $t['sections'];
            }
        }

        return [];
    }

    /**
     * @return array{id: string, category: string, label: string, description: string, icon: string, slug: string, title: string, seo: string, publish: bool, sections: list<array{type: string, variant: string}>}|null
     */
    public static function find(string $id): ?array
    {
        foreach (self::all() as $t) {
            if ($t['id'] === $id) {
                return $t;
            }
        }

        return null;
    }

    /**
     * @return list<array{id: string, category: string, label: string, description: string, icon: string, slug: string, title: string, seo: string, publish: bool, sections: list<array{type: string, variant: string}>}>
     */
    private static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $templates = [
            self::blank(),
        ];

        $landingCombos = [
            [['hero', 'centered'], ['logos', 'row'], ['features', 'grid-3'], ['testimonials', 'grid'], ['cta', 'banner']],
            [['hero', 'split'], ['features', 'grid-3'], ['stats', 'row'], ['cta', 'centered']],
            [['hero', 'fullscreen'], ['stats', 'cards'], ['features', 'bento'], ['newsletter', 'centered']],
            [['hero', 'badge'], ['features', 'grid-2'], ['steps', 'row'], ['cta', 'banner']],
            [['hero', 'image-below'], ['logos', 'marquee'], ['pricing', 'cards'], ['faq', 'list']],
            [['hero', 'video'], ['integrations', 'grid-3'], ['testimonials', 'featured'], ['cta', 'cards']],
            [['hero', 'minimal'], ['features', 'list'], ['cta', 'centered']],
            [['hero', 'split-left'], ['about', 'split'], ['stats', 'row'], ['cta', 'banner']],
            [['hero', 'centered'], ['compare', 'table'], ['testimonials', 'grid'], ['newsletter', 'inline']],
            [['hero', 'fullscreen'], ['waitlist', 'centered'], ['logos', 'row'], ['faq', 'list']],
            [['hero', 'badge'], ['demo', 'centered'], ['features', 'grid-3'], ['cta', 'banner']],
            [['hero', 'split'], ['steps', 'vertical'], ['pricing', 'cards'], ['cta', 'centered']],
            [['hero', 'centered'], ['gallery', 'grid'], ['testimonials', 'masonry'], ['cta', 'banner']],
            [['hero', 'image-below'], ['services', 'grid-3'], ['process', 'row'], ['cta', 'cards']],
            [['hero', 'fullscreen'], ['awards', 'grid'], ['stats', 'row'], ['signup', 'centered']],
            [['hero', 'centered'], ['community', 'grid'], ['blog', 'list'], ['cta', 'banner']],
            [['hero', 'split'], ['timeline', 'vertical'], ['team', 'grid'], ['cta', 'centered']],
            [['hero', 'badge'], ['highlights', 'grid-3'], ['compare', 'table'], ['cta', 'banner']],
            [['hero', 'video'], ['projects', 'grid-3'], ['testimonials', 'grid-2'], ['newsletter', 'banner']],
            [['hero', 'minimal'], ['logos', 'row'], ['cta', 'split'], ['faq', 'two-col']],
        ];
        foreach ($landingCombos as $i => $sections) {
            $n = $i + 1;
            $templates[] = self::tpl(
                'landing-' . str_pad((string) $n, 2, '0', STR_PAD_LEFT),
                'landing',
                'Landing ' . $n,
                'Composition marketing ' . $n . ' : hero, preuve sociale et conversion.',
                'fa-solid fa-rocket',
                $n === 1 ? '' : 'landing-' . $n,
                $n === 1 ? 'Accueil' : 'Landing ' . $n,
                'Page d\'accueil marketing, variante ' . $n . '.',
                $sections,
            );
        }

        $blogCombos = [
            [['hero', 'centered'], ['blog', 'grid-3'], ['newsletter', 'inline'], ['cta', 'centered']],
            [['hero', 'minimal'], ['blog', 'list'], ['cta', 'banner']],
            [['hero', 'split'], ['blog', 'featured'], ['blog', 'grid-2'], ['newsletter', 'centered']],
        ];
        foreach ($blogCombos as $i => $sections) {
            $n = $i + 1;
            $templates[] = self::tpl('blog-' . $n, 'blog', 'Blog ' . $n, 'Liste d\'articles, variante ' . $n . '.', 'fa-solid fa-newspaper', $n === 1 ? 'blog' : 'blog-' . $n, $n === 1 ? 'Blog' : 'Blog ' . $n, 'Articles et actualités.', $sections);
        }

        $blogPostSections = [
            [['hero', 'minimal'], ['content', 'prose'], ['cta', 'centered']],
            [['hero', 'badge'], ['content', 'columns-2'], ['newsletter', 'inline']],
        ];
        foreach ($blogPostSections as $i => $sections) {
            $n = $i + 1;
            $templates[] = self::tpl('blog-post-' . $n, 'blog', 'Article ' . $n, 'Page article, variante ' . $n . '.', 'fa-solid fa-file-lines', 'article-' . $n, 'Article ' . $n, 'Contenu éditorial.', $sections);
        }

        $pricingCombos = [
            [['hero', 'centered'], ['pricing', 'cards'], ['compare', 'table'], ['faq', 'list'], ['cta', 'banner']],
            [['hero', 'minimal'], ['pricing', 'cards'], ['cta', 'centered']],
            [['hero', 'split'], ['pricing', 'cards'], ['testimonials', 'grid'], ['cta', 'banner']],
            [['hero', 'centered'], ['stats', 'row'], ['pricing', 'cards'], ['faq', 'two-col']],
            [['hero', 'badge'], ['compare', 'table'], ['pricing', 'cards'], ['cta', 'cards']],
        ];
        foreach ($pricingCombos as $i => $sections) {
            $n = $i + 1;
            $templates[] = self::tpl('pricing-' . $n, 'pricing', 'Tarifs ' . $n, 'Page tarifs, variante ' . $n . '.', 'fa-solid fa-tags', $n === 1 ? 'tarifs' : 'tarifs-' . $n, $n === 1 ? 'Tarifs' : 'Tarifs ' . $n, 'Formules et prix transparents.', $sections);
        }

        $changelogCombos = [
            [['hero', 'centered'], ['changelog', 'list']],
            [['hero', 'minimal'], ['changelog', 'list'], ['newsletter', 'inline']],
            [['hero', 'centered'], ['timeline', 'vertical'], ['changelog', 'list']],
            [['content', 'prose'], ['changelog', 'list'], ['cta', 'centered']],
        ];
        foreach ($changelogCombos as $i => $sections) {
            $n = $i + 1;
            $templates[] = self::tpl('changelog-' . $n, 'changelog', 'Changelog ' . $n, 'Journal des versions ' . $n . '.', 'fa-solid fa-clock-rotate-left', $n === 1 ? 'changelog' : 'changelog-' . $n, $n === 1 ? 'Changelog' : 'Changelog ' . $n, 'Notes de version.', $sections);
        }

        $aboutCombos = [
            [['hero', 'centered'], ['about', 'split'], ['stats', 'row'], ['team', 'grid'], ['cta', 'banner']],
            [['hero', 'image-below'], ['timeline', 'vertical'], ['team', 'grid'], ['cta', 'centered']],
            [['hero', 'split'], ['about', 'split'], ['awards', 'grid'], ['cta', 'banner']],
        ];
        foreach ($aboutCombos as $i => $sections) {
            $n = $i + 1;
            $templates[] = self::tpl('about-' . $n, 'about', 'À propos ' . $n, 'Page entreprise, variante ' . $n . '.', 'fa-solid fa-building', $n === 1 ? 'about' : 'about-' . $n, $n === 1 ? 'À propos' : 'À propos ' . $n, 'Mission et équipe.', $sections);
        }

        $contactCombos = [
            [['hero', 'centered'], ['contact', 'cards'], ['faq', 'list']],
            [['hero', 'split'], ['contact', 'split'], ['cta', 'centered']],
            [['hero', 'minimal'], ['contact', 'list'], ['contact', 'centered']],
        ];
        foreach ($contactCombos as $i => $sections) {
            $n = $i + 1;
            $templates[] = self::tpl('contact-' . $n, 'contact', 'Contact ' . $n, 'Page contact, variante ' . $n . '.', 'fa-solid fa-envelope', $n === 1 ? 'contact' : 'contact-' . $n, $n === 1 ? 'Contact' : 'Contact ' . $n, 'Nous contacter.', $sections);
        }

        $integrationCombos = [
            [['hero', 'centered'], ['integrations', 'grid-3'], ['logos', 'row'], ['cta', 'banner']],
            [['hero', 'split'], ['integrations', 'row'], ['features', 'grid-3'], ['testimonials', 'grid']],
            [['hero', 'badge'], ['integrations', 'list'], ['compare', 'table'], ['cta', 'centered']],
        ];
        foreach ($integrationCombos as $i => $sections) {
            $n = $i + 1;
            $templates[] = self::tpl('integrations-' . $n, 'integrations', 'Intégrations ' . $n, 'Page intégrations, variante ' . $n . '.', 'fa-solid fa-plug', $n === 1 ? 'integrations' : 'integrations-' . $n, $n === 1 ? 'Intégrations' : 'Intégrations ' . $n, 'Outils compatibles.', $sections);
        }

        $faqCombos = [
            [['hero', 'centered'], ['faq', 'list'], ['cta', 'banner']],
            [['hero', 'minimal'], ['faq', 'two-col'], ['contact', 'cards']],
        ];
        foreach ($faqCombos as $i => $sections) {
            $n = $i + 1;
            $templates[] = self::tpl('faq-' . $n, 'faq', 'FAQ ' . $n, 'Questions fréquentes, variante ' . $n . '.', 'fa-solid fa-circle-question', $n === 1 ? 'faq' : 'faq-' . $n, $n === 1 ? 'FAQ' : 'FAQ ' . $n, 'Réponses aux questions courantes.', $sections);
        }

        $productCombos = [
            [['hero', 'split'], ['features', 'grid-3'], ['pricing', 'cards'], ['demo', 'centered'], ['cta', 'banner']],
            [['hero', 'fullscreen'], ['stats', 'cards'], ['features', 'bento'], ['testimonials', 'featured'], ['cta', 'cards']],
        ];
        foreach ($productCombos as $i => $sections) {
            $n = $i + 1;
            $templates[] = self::tpl('product-' . $n, 'product', 'Produit ' . $n, 'Page produit, variante ' . $n . '.', 'fa-solid fa-cube', $n === 1 ? 'produit' : 'produit-' . $n, $n === 1 ? 'Produit' : 'Produit ' . $n, 'Présentation produit.', $sections);
        }

        $featureCombos = [
            [['hero', 'split'], ['features', 'grid-3'], ['stats', 'row'], ['cta', 'banner']],
            [['hero', 'centered'], ['features', 'bento'], ['compare', 'table'], ['cta', 'centered']],
        ];
        foreach ($featureCombos as $i => $sections) {
            $n = $i + 1;
            $templates[] = self::tpl('feature-' . $n, 'feature', 'Fonctionnalités ' . $n, 'Page fonctionnalités, variante ' . $n . '.', 'fa-solid fa-table-cells-large', $n === 1 ? 'fonctionnalites' : 'fonctionnalites-' . $n, $n === 1 ? 'Fonctionnalités' : 'Fonctionnalités ' . $n, 'Points clés du produit.', $sections);
        }

        self::$cache = $templates;

        return self::$cache;
    }

    /**
     * @return array{id: string, category: string, label: string, description: string, icon: string, slug: string, title: string, seo: string, publish: bool, sections: list<array{type: string, variant: string}>}
     */
    private static function blank(): array
    {
        return self::tpl('blank', 'blank', 'Page vide', 'Aucun bloc, composition libre.', 'fa-solid fa-file', '', '', '', [], false);
    }

    /**
     * @param list<array{0: string, 1: string}> $sections
     *
     * @return array{id: string, category: string, label: string, description: string, icon: string, slug: string, title: string, seo: string, publish: bool, sections: list<array{type: string, variant: string}>}
     */
    private static function tpl(
        string $id,
        string $category,
        string $label,
        string $description,
        string $icon,
        string $slug,
        string $title,
        string $seo,
        array $sections,
        bool $publish = true,
    ): array {
        $refs = [];
        foreach ($sections as $pair) {
            $refs[] = ['type' => $pair[0], 'variant' => $pair[1]];
        }

        return [
            'id' => $id,
            'category' => $category,
            'label' => $label,
            'description' => $description,
            'icon' => $icon,
            'slug' => $slug,
            'title' => $title,
            'seo' => $seo,
            'publish' => $publish,
            'sections' => $refs,
        ];
    }
}
