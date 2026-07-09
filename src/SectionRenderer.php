<?php

declare(strict_types=1);

namespace Capsule;

use Capsule\MediaDisplaySettings;
use Capsule\Support\Utf8;

final class SectionRenderer
{
    public function __construct(
        private readonly View $view,
        private readonly string $sectionsDir,
        private readonly bool $isDev = true,
    ) {
    }

    /**
     * @param list<array<string, mixed>> $sections
     */
    public function renderAll(array $sections): string
    {
        $html = [];
        foreach ($sections as $section) {
            $rendered = $this->renderOne($section);
            if ($rendered !== '') {
                $html[] = $rendered;
            }
        }

        return implode("\n", $html);
    }

    /**
     * @param array<string, mixed> $section
     */
    public function renderOne(array $section): string
    {
        if (array_key_exists('visible', $section) && $section['visible'] === false) {
            return '';
        }

        $type = is_string($section['type'] ?? null) ? $section['type'] : '';
        $variant = is_string($section['variant'] ?? null) ? $section['variant'] : 'default';
        if ($type === 'hero') {
            $variant = HeroStyle::normalizeVariant($variant);
            $section['variant'] = $variant;
        }
        if ($type === 'features') {
            $variant = FeatureStyle::normalizeVariant($variant);
            $section['variant'] = $variant;
        }
        if ($type === 'integrations') {
            $variant = IntegrationStyle::normalizeVariant($variant);
            $section['variant'] = $variant;
        }
        if ($type === 'pricing') {
            $variant = PricingStyle::normalizeVariant($variant);
            $section['variant'] = $variant;
        }
        if ($type === 'contact') {
            $variant = ContactStyle::normalizeVariant($variant);
            $section['variant'] = $variant;
        }
        if ($type === 'testimonials') {
            $variant = TestimonialStyle::normalizeVariant($variant);
            $section['variant'] = $variant;
        }
        if ($type === 'gallery') {
            $variant = GalleryStyle::normalizeVariant($variant);
            $section['variant'] = $variant;
        }
        if ($type === '') {
            return '';
        }

        $template = $this->resolveTemplate($type, $variant);
        if ($template === null) {
            if ($this->isDev) {
                return '<!-- section missing: ' . htmlspecialchars($type . '/' . $variant, ENT_QUOTES) . ' -->';
            }

            return '';
        }

        $data = $this->buildTemplateData($section);

        return $this->view->renderString($template, $data);
    }

    /**
     * @return list<array{type: string, variant: string}>
     */
    public function extractSectionRefs(array $sections): array
    {
        $refs = [];
        foreach ($sections as $section) {
            if (!is_array($section)) {
                continue;
            }
            $type = is_string($section['type'] ?? null) ? $section['type'] : '';
            $variant = is_string($section['variant'] ?? null) ? $section['variant'] : 'default';
            if ($type === 'hero') {
                $variant = HeroStyle::normalizeVariant($variant);
            }
            if ($type === 'features') {
                $variant = FeatureStyle::normalizeVariant($variant);
            }
            if ($type === 'integrations') {
                $variant = IntegrationStyle::normalizeVariant($variant);
            }
            if ($type === 'pricing') {
                $variant = PricingStyle::normalizeVariant($variant);
            }
            if ($type === 'contact') {
                $variant = ContactStyle::normalizeVariant($variant);
            }
            if ($type === 'testimonials') {
                $variant = TestimonialStyle::normalizeVariant($variant);
            }
            if ($type === 'gallery') {
                $variant = GalleryStyle::normalizeVariant($variant);
            }
            if ($type !== '') {
                $refs[] = ['type' => $type, 'variant' => $variant];
            }
        }

        return $refs;
    }

    private function resolveTemplate(string $type, string $variant): ?string
    {
        $safeType = $this->safeName($type);
        $safeVariant = $this->safeName($variant);
        $candidates = [
            $this->sectionsDir . '/' . $safeType . '/' . $safeVariant . '.html',
        ];
        foreach (SectionLayoutFamilies::htmlFamilies($safeVariant) as $family) {
            $candidates[] = $this->sectionsDir . '/' . $safeType . '/' . $family . '.html';
        }
        $candidates[] = $this->sectionsDir . '/' . $safeType . '/default.html';

        foreach ($candidates as $file) {
            if (!is_file($file)) {
                continue;
            }
            $content = file_get_contents($file);

            return $content !== false ? $content : null;
        }

        return null;
    }

    /**
     * @param array<string, mixed> $section
     *
     * @return array<string, mixed>
     */
    private function buildTemplateData(array $section): array
    {
        $data = [];
        $data['section_id'] = is_string($section['id'] ?? null) ? $section['id'] : '';
        $data['type'] = is_string($section['type'] ?? null) ? $section['type'] : '';
        $data['variant'] = is_string($section['variant'] ?? null) ? $section['variant'] : 'default';

        $content = is_array($section['content'] ?? null) ? $section['content'] : [];
        foreach ($content as $key => $value) {
            if (is_scalar($value)) {
                $data['content_' . $key] = (string) $value;
            }
        }

        $style = is_array($section['style'] ?? null) ? $section['style'] : [];
        foreach ($style as $key => $value) {
            if (is_scalar($value)) {
                $data['style_' . $key] = (string) $value;
            }
        }

        if (isset($content['items']) && is_array($content['items'])) {
            $data['items_html'] = $this->renderItems($content['items'], $data['type'], $data['variant']);
        }

        $data['head_html'] = $this->buildHeadHtml($content);
        $data['buttons_html'] = $this->resolveButtonsHtml($content);

        if (is_string($content['text'] ?? null)) {
            $paragraphs = array_values(array_filter(
                array_map('trim', preg_split('/\r\n|\n|\r/', $content['text']) ?: []),
                static fn (string $p): bool => $p !== '',
            ));
            $data['text_paragraphs_html'] = implode('', array_map(
                static fn (string $p): string => '<p>' . htmlspecialchars($p, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>',
                $paragraphs,
            ));
        }

        $sectionTitle = trim((string) ($content['title'] ?? ''));
        $data['optional_section_title_html'] = $sectionTitle !== ''
            ? '<h2 class="section-content__title">' . htmlspecialchars($sectionTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</h2>'
            : '';

        $bannerHref = trim((string) ($content['href'] ?? ''));
        $bannerLinkLabel = trim((string) ($content['link_label'] ?? ''));
        $data['banner_link_html'] = ($bannerHref !== '' && $bannerLinkLabel !== '')
            ? ' <a class="section-banner__link" href="' . htmlspecialchars($bannerHref, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">'
                . htmlspecialchars($bannerLinkLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</a>'
            : '';

        $badgeText = trim((string) ($content['badge'] ?? ''));
        if ($badgeText === '' && $data['variant'] === 'badge') {
            $badgeText = 'Nouveau';
        }
        $data['badge_html'] = $badgeText !== ''
            ? '<span class="section-hero__badge">' . htmlspecialchars($badgeText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span>'
            : '';

        $data['hero_modifiers'] = '';
        $data['hero_backdrop_html'] = '';
        $data['hero_backdrop_class'] = '';

        if ($data['type'] === 'hero') {
            $resolvedStyle = HeroStyle::resolve($style, $data['variant']);
            foreach ($resolvedStyle as $key => $value) {
                $data['style_' . $key] = $value;
            }
            $data['hero_modifiers'] = HeroStyle::modifierClasses($style, $data['variant']);
            $badgeText = trim((string) ($content['badge'] ?? ''));
            $data['hero_badge_html'] = $badgeText !== ''
                ? '<span class="section-hero__badge">' . htmlspecialchars($badgeText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span>'
                : '';
            $subheading = trim((string) ($content['subheading'] ?? ''));
            $data['hero_subheading_html'] = $subheading !== ''
                ? ' <span class="section-hero__subheading">' . htmlspecialchars($subheading, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span>'
                : '';
            $data = HeroVariantRenderer::enrich($data, $content, $data['variant'], $this->renderHeroButtons($content));
        } elseif ($data['type'] === 'features') {
            $resolvedStyle = FeatureStyle::resolve($style, $data['variant']);
            foreach ($resolvedStyle as $key => $value) {
                $data['style_' . $key] = $value;
            }
            $data = FeatureVariantRenderer::enrich($data, $content, $data['variant']);
        } elseif ($data['type'] === 'integrations') {
            $resolvedStyle = IntegrationStyle::resolve($style, $data['variant']);
            foreach ($resolvedStyle as $key => $value) {
                $data['style_' . $key] = $value;
            }
            $data = IntegrationVariantRenderer::enrich($data, $content, $data['variant']);
        } elseif ($data['type'] === 'pricing') {
            $resolvedStyle = PricingStyle::resolve($style, $data['variant']);
            foreach ($resolvedStyle as $key => $value) {
                $data['style_' . $key] = $value;
            }
            $data = PricingVariantRenderer::enrich($data, $content, $data['variant']);
        } elseif ($data['type'] === 'contact') {
            $resolvedStyle = ContactStyle::resolve($style, $data['variant']);
            foreach ($resolvedStyle as $key => $value) {
                $data['style_' . $key] = $value;
            }
            $data = ContactVariantRenderer::enrich($data, $content, $data['variant']);
        } elseif ($data['type'] === 'testimonials') {
            $resolvedStyle = TestimonialStyle::resolve($style, $data['variant']);
            foreach ($resolvedStyle as $key => $value) {
                $data['style_' . $key] = $value;
            }
            $data = TestimonialVariantRenderer::enrich($data, $content, $data['variant']);
        } elseif ($data['type'] === 'gallery') {
            $resolvedStyle = GalleryStyle::resolve($style, $data['variant']);
            foreach ($resolvedStyle as $key => $value) {
                $data['style_' . $key] = $value;
            }
            $data = GalleryVariantRenderer::enrich($data, $content, $data['variant']);
        } else {
            $imageUrl = MediaDisplaySettings::normalizeUrl((string) ($content['image_url'] ?? ''));
            $imageTitle = trim((string) ($content['title'] ?? ''));
            $safeAlt = htmlspecialchars($imageTitle !== '' ? $imageTitle : 'Illustration', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $data['hero_visual_html'] = $imageUrl !== ''
                ? '<img class="section-hero__img" src="' . htmlspecialchars($imageUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                    . '" alt="' . $safeAlt . '" loading="lazy" decoding="async" />'
                : '<div class="section-hero__visual-placeholder" aria-hidden="true"></div>';
            $data['feature_image_html'] = $this->featureSectionImageHtml($data['type'], $data['variant'], $content);
            $data['quote_html'] = $this->featureQuoteHtml($data['type'], $content);
            $data['checklist_html'] = $this->featureChecklistHtml($data['type'], $data['variant'], $content);
        }

        $codeText = trim((string) ($content['code'] ?? $content['text'] ?? ''));
        $data['code_html'] = $codeText !== ''
            ? '<pre class="section-code"><code>' . htmlspecialchars($codeText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</code></pre>'
            : '';

        return $data;
    }

    /**
     * En-tête de section optionnel (titre + sous-titre), rendu seulement si renseigné
     * pour éviter des balises de titre vides.
     *
     * @param array<string, mixed> $content
     */
    private function buildHeadHtml(array $content): string
    {
        $title = trim((string) ($content['title'] ?? ''));
        $subtitle = trim((string) ($content['subtitle'] ?? ''));
        if ($title === '' && $subtitle === '') {
            return '';
        }

        $html = '<div class="section-head">';
        if ($title !== '') {
            $html .= '<h2 class="section-head__title">' . htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</h2>';
        }
        if ($subtitle !== '') {
            $html .= '<p class="section-head__subtitle">' . htmlspecialchars($subtitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
        }

        return $html . '</div>';
    }

    /**
     * @param array<string, mixed> $content
     */
    private function resolveButtonsHtml(array $content): string
    {
        if (isset($content['buttons']) && is_array($content['buttons'])) {
            return $this->renderButtons($content['buttons']);
        }

        // Compatibilité ascendante : anciennes sections enregistrées avant l'introduction
        // du répéteur de boutons (un seul lien plat en contenu).
        $legacyLabel = $content['cta_label'] ?? $content['button_label'] ?? null;
        $legacyHref = $content['cta_href'] ?? $content['button_href'] ?? null;
        if ($legacyLabel !== null || $legacyHref !== null) {
            return $this->renderButtons([[
                'label' => $legacyLabel ?? '',
                'href' => $legacyHref ?? '',
                'style' => 'primary',
            ]]);
        }

        return '';
    }

    /**
     * @param list<mixed> $buttons
     */
    private function renderButtons(array $buttons): string
    {
        $parts = [];
        foreach ($buttons as $button) {
            if (!is_array($button)) {
                continue;
            }
            $label = trim((string) ($button['label'] ?? ''));
            $href = trim((string) ($button['href'] ?? ''));
            if ($label === '' || $href === '') {
                continue;
            }
            $style = ($button['style'] ?? 'primary') === 'secondary' ? 'secondary' : 'primary';
            $parts[] = '<a class="section-button section-button--' . $style . '" href="'
                . htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">'
                . htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</a>';
        }

        return implode("\n", $parts);
    }

    /**
     * @param array<string, mixed> $content
     */
    private function renderHeroButtons(array $content): string
    {
        $buttons = $content['buttons'] ?? null;
        if (!is_array($buttons) || $buttons === []) {
            $legacyLabel = trim((string) ($content['cta_label'] ?? $content['button_label'] ?? ''));
            $legacyHref = trim((string) ($content['cta_href'] ?? $content['button_href'] ?? ''));
            if ($legacyLabel === '' || $legacyHref === '') {
                return '';
            }

            $buttons = [['label' => $legacyLabel, 'href' => $legacyHref, 'style' => 'primary']];
        }

        $parts = [];
        $isFirstPrimary = true;
        foreach ($buttons as $button) {
            if (!is_array($button)) {
                continue;
            }
            $label = trim((string) ($button['label'] ?? ''));
            $href = trim((string) ($button['href'] ?? ''));
            if ($label === '' || $href === '') {
                continue;
            }
            $style = ($button['style'] ?? 'primary') === 'secondary' ? 'secondary' : 'primary';
            $icon = ($style === 'primary' && $isFirstPrimary)
                ? ' <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>'
                : '';
            if ($style === 'primary') {
                $isFirstPrimary = false;
            }
            $parts[] = '<a class="section-hero__button section-hero__button--' . $style . '" href="'
                . htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">'
                . htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . $icon . '</a>';
        }

        return implode("\n", $parts);
    }

    /**
     * @param list<mixed> $items
     */
    private function renderItems(array $items, string $type, string $variant): string
    {
        $template = $this->resolveItemTemplate($type, $variant);

        $parts = [];
        $index = 0;
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $index++;

            if ($template === null) {
                $title = htmlspecialchars((string) ($item['title'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $text = htmlspecialchars((string) ($item['text'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $parts[] = '<article class="section-features__item"><h3>' . $title . '</h3><p>' . $text . '</p></article>';
                continue;
            }

            $parts[] = $this->view->renderString($template, $this->itemData($item, $index, $type, $variant));
        }

        return implode("\n", $parts);
    }

    /**
     * Données de rendu d'un élément de répéteur : champs bruts (échappés par le
     * template via {{...}}) plus variantes précalculées (listes, initiales, bouton).
     *
     * @param array<string, mixed> $item
     *
     * @return array<string, mixed>
     */
    private function itemData(array $item, int $index, string $type, string $variant = 'default'): array
    {
        $data = ['index' => (string) $index];

        foreach ($item as $key => $value) {
            if (!is_scalar($value)) {
                continue;
            }
            $str = (string) $value;
            $data[(string) $key] = $str;

            $lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\n|\r/', $str) ?: []), static fn (string $l): bool => $l !== ''));
            $data[(string) $key . '_lines_html'] = implode('', array_map(
                static fn (string $line): string => '<li>' . htmlspecialchars($line, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</li>',
                $lines,
            ));
        }

        $title = trim((string) ($item['title'] ?? ''));
        $itemHref = trim((string) ($item['href'] ?? ''));
        $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $data['title_linked_html'] = ($title !== '' && $itemHref !== '')
            ? '<a href="' . htmlspecialchars($itemHref, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">' . $safeTitle . '</a>'
            : $safeTitle;

        $words = preg_split('/\s+/', $title) ?: [];
        $initials = '';
        foreach (array_slice(array_filter($words), 0, 2) as $word) {
            $initials .= Utf8::strtoupper(Utf8::substr($word, 0, 1));
        }
        $data['initials'] = $initials;

        $rawIcon = trim((string) ($item['icon'] ?? ''));
        if ($type === 'features' && $rawIcon === '') {
            $rawIcon = FontAwesomeIcon::defaultForIndex($index);
        }
        $glyph = $rawIcon !== '' ? FontAwesomeIcon::glyph($rawIcon) : '';
        $data['icon'] = $glyph;
        $data['icon_class'] = $glyph !== '' ? FontAwesomeIcon::solidClass($glyph) : '';
        $data['index_padded'] = str_pad((string) $index, 2, '0', STR_PAD_LEFT);
        $data['featured_class'] = match (true) {
            $type === 'pricing' && $index === 2 => ' section-pricing__card--featured',
            $type === 'features' && $variant === 'feature-5' && $index === 1 => ' section-features__item--lead',
            default => '',
        };

        $imageUrl = MediaDisplaySettings::normalizeUrl((string) ($item['url'] ?? ''));
        $data['image_html'] = $this->itemImageHtml($type, $imageUrl, $title, $item);

        $role = trim((string) ($item['role'] ?? ''));
        $date = trim((string) ($item['date'] ?? ''));
        $metaParts = [];
        if ($role !== '') {
            $metaParts[] = '<span class="section-item__meta-part">' . htmlspecialchars($role, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span>';
        }
        if ($date !== '') {
            $metaParts[] = '<span class="section-item__meta-part">' . htmlspecialchars($date, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span>';
        }
        $data['meta_html'] = $metaParts !== []
            ? '<p class="section-' . $this->safeName($type) . '__meta">' . implode('<span class="section-item__meta-sep" aria-hidden="true">·</span>', $metaParts) . '</p>'
            : '';

        $data['read_more_html'] = ($type === 'blog' && $itemHref !== '')
            ? '<a class="section-blog__link" href="' . htmlspecialchars($itemHref, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">'
                . 'Lire l\'article <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>'
            : '';

        $mediaHref = $itemHref;
        if ($mediaHref === '' && $type === 'projects') {
            $mediaHref = '';
        }
        $data['media_link_open'] = ($mediaHref !== '' && in_array($type, ['blog', 'projects'], true))
            ? '<a class="section-' . $this->safeName($type) . '__media" href="' . htmlspecialchars($mediaHref, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">'
            : '<div class="section-' . $this->safeName($type) . '__media">';
        $data['media_link_close'] = ($mediaHref !== '' && in_array($type, ['blog', 'projects'], true)) ? '</a>' : '</div>';

        $ctaLabel = trim((string) ($item['cta_label'] ?? ''));
        $ctaHref = trim((string) ($item['cta_href'] ?? ''));
        $data['cta_html'] = ($ctaLabel !== '' && $ctaHref !== '')
            ? '<a class="section-button section-button--primary" href="' . htmlspecialchars($ctaHref, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">'
                . htmlspecialchars($ctaLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</a>'
            : '';

        // Valeur cliquable si un lien est fourni (ex. mailto:, tel:), sinon texte simple.
        $text = trim((string) ($item['text'] ?? ''));
        $href = trim((string) ($item['href'] ?? ''));
        $safeText = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $data['text_linked_html'] = ($text !== '' && $href !== '')
            ? '<a href="' . htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">' . $safeText . '</a>'
            : $safeText;

        return $data;
    }

    /**
     * @param array<string, mixed> $item
     */
    private function itemImageHtml(string $type, string $imageUrl, string $title, array $item = []): string
    {
        $prefix = 'section-' . $this->safeName($type);
        $safeAlt = htmlspecialchars($title !== '' ? $title : 'Image', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        if ($imageUrl === '') {
            return '<div class="' . $prefix . '__placeholder" aria-hidden="true"><i class="fa-solid fa-image"></i></div>';
        }

        $fitClass = MediaDisplaySettings::imageFitClass($item, $prefix . '__img');

        return '<img class="' . $prefix . '__img ' . $fitClass . '" src="' . htmlspecialchars($imageUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            . '" alt="' . $safeAlt . '" width="640" height="480" loading="lazy" decoding="async" />';
    }

    private function resolveItemTemplate(string $type, string $variant): ?string
    {
        $safeType = $this->safeName($type);
        $safeVariant = $this->safeName($variant);
        $candidates = [
            $this->sectionsDir . '/' . $safeType . '/item-' . $safeVariant . '.html',
            $this->sectionsDir . '/' . $safeType . '/item.html',
        ];

        foreach ($candidates as $file) {
            if (!is_file($file)) {
                continue;
            }
            $content = file_get_contents($file);

            return $content !== false ? $content : null;
        }

        return null;
    }

    private function safeName(string $name): string
    {
        $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '', $name) ?? '';

        return $sanitized !== '' ? $sanitized : 'default';
    }

    /**
     * @param array<string, mixed> $content
     */
    private function featureSectionImageHtml(string $type, string $variant, array $content): string
    {
        if ($type !== 'features' || !in_array($variant, ['feature-1', 'feature-2', 'feature-6', 'feature-7', 'feature-9'], true)) {
            return '';
        }

        $imageUrl = MediaDisplaySettings::normalizeUrl((string) ($content['image_url'] ?? ''));
        $title = trim((string) ($content['title'] ?? ''));
        $safeAlt = htmlspecialchars($title !== '' ? $title : 'Illustration', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $frameClass = $variant === 'feature-7' ? ' section-features__figure--bordered' : '';

        if ($imageUrl === '') {
            return '<div class="section-features__figure' . $frameClass . '"><div class="section-features__placeholder" aria-hidden="true"><i class="fa-solid fa-image"></i></div></div>';
        }

        return '<div class="section-features__figure' . $frameClass . '"><img class="section-features__img section-features__img--fit-cover" src="'
            . htmlspecialchars($imageUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            . '" alt="' . $safeAlt . '" width="640" height="640" loading="lazy" decoding="async" /></div>';
    }

    /**
     * @param array<string, mixed> $content
     */
    private function featureQuoteHtml(string $type, array $content): string
    {
        if ($type !== 'features') {
            return '';
        }

        $quote = trim((string) ($content['quote_text'] ?? ''));
        if ($quote === '') {
            return '';
        }

        $author = trim((string) ($content['quote_author'] ?? ''));
        $html = '<blockquote class="section-features__quote"><p>' . htmlspecialchars($quote, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
        if ($author !== '') {
            $html .= '<footer class="section-features__quote-author">' . htmlspecialchars($author, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</footer>';
        }

        return $html . '</blockquote>';
    }

    /**
     * @param array<string, mixed> $content
     */
    private function featureChecklistHtml(string $type, string $variant, array $content): string
    {
        if ($type !== 'features' || !in_array($variant, ['feature-6', 'feature-7'], true)) {
            return '';
        }

        $items = is_array($content['items'] ?? null) ? $content['items'] : [];
        $parts = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $line = trim((string) ($item['title'] ?? ''));
            if ($line === '') {
                $line = trim((string) ($item['text'] ?? ''));
            }
            if ($line === '') {
                continue;
            }
            $parts[] = '<li class="section-features__check-item"><i class="fa-solid fa-check" aria-hidden="true"></i><span>'
                . htmlspecialchars($line, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span></li>';
        }

        return $parts === [] ? '' : '<ul class="section-features__checklist">' . implode('', $parts) . '</ul>';
    }
}
