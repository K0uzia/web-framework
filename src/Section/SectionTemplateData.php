<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\MediaDisplaySettings;
use Capsule\Section\Support\SectionButtons;
use Capsule\View;

/**
 * Mapping JSON section → variables template.
 */
final class SectionTemplateData
{
    public function __construct(
        private readonly View $view,
        private readonly SectionHandlerRegistry $handlers,
    ) {
    }

    /**
     * @param array<string, mixed> $section
     *
     * @return array<string, mixed>
     */
    public function build(array $section): array
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

        if (!isset($data['style_bg']) || trim($data['style_bg']) === '') {
            $data['style_bg'] = 'background';
        }

        $data['head_html'] = $this->buildHeadHtml($content);
        $data['buttons_html'] = SectionButtons::resolveHtml($content);

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

        $handler = $this->handlers->get($data['type']);
        if ($handler !== null) {
            $data['variant'] = $handler->normalizeVariant($data['variant']);
            $resolvedStyle = $handler->resolveStyle($style, $data['variant']);
            foreach ($resolvedStyle as $key => $value) {
                $data['style_' . $key] = $value;
            }
            $context = new SectionEnrichContext(SectionButtons::resolveHeroHtml(...));
            $data = $handler->enrich($data, $content, $data['variant'], $context);
        }

        $codeText = trim((string) ($content['code'] ?? $content['text'] ?? ''));
        $data['code_html'] = $codeText !== ''
            ? '<pre class="section-code"><code>' . htmlspecialchars($codeText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</code></pre>'
            : '';

        return $data;
    }

    /**
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
        $html .= '</div>';

        return $html;
    }
}
