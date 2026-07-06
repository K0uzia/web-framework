<?php

declare(strict_types=1);

namespace Capsule;

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
        $data['badge_html'] = $badgeText !== ''
            ? '<span class="section-hero__badge">' . htmlspecialchars($badgeText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span>'
            : '';

        $imageUrl = trim((string) ($content['image_url'] ?? ''));
        $imageTitle = trim((string) ($content['title'] ?? ''));
        $safeAlt = htmlspecialchars($imageTitle !== '' ? $imageTitle : 'Illustration', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $data['hero_visual_html'] = $imageUrl !== ''
            ? '<img class="section-hero__img" src="' . htmlspecialchars($imageUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                . '" alt="' . $safeAlt . '" loading="lazy" decoding="async" />'
            : '<div class="section-hero__visual-placeholder" aria-hidden="true"></div>';

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

            $parts[] = $this->view->renderString($template, $this->itemData($item, $index));
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
    private function itemData(array $item, int $index): array
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
            $initials .= mb_strtoupper(mb_substr($word, 0, 1));
        }
        $data['initials'] = $initials;

        $imageUrl = trim((string) ($item['url'] ?? ''));
        $safeAlt = htmlspecialchars($title !== '' ? $title : 'Image', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $data['image_html'] = $imageUrl !== ''
            ? '<img class="section-gallery__img" src="' . htmlspecialchars($imageUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                . '" alt="' . $safeAlt . '" loading="lazy" decoding="async" />'
            : '<div class="section-gallery__placeholder" aria-hidden="true"><i class="fa-solid fa-image"></i></div>';

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
}
