<?php

declare(strict_types=1);

namespace Capsule\Section\Support;

final class SectionButtons
{
    /**
     * @param array<string, mixed> $content
     */
    public static function resolveHtml(array $content): string
    {
        if (isset($content['buttons']) && is_array($content['buttons'])) {
            return self::render($content['buttons']);
        }

        $legacyLabel = $content['cta_label'] ?? $content['button_label'] ?? null;
        $legacyHref = $content['cta_href'] ?? $content['button_href'] ?? null;
        if ($legacyLabel !== null || $legacyHref !== null) {
            return self::render([[
                'label' => $legacyLabel ?? '',
                'href' => $legacyHref ?? '',
                'style' => 'primary',
            ]]);
        }

        return '';
    }

    /**
     * @param array<string, mixed> $content
     */
    public static function resolveHeroHtml(array $content): string
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
            $style = SectionButtonStyle::normalize((string) ($button['style'] ?? 'primary'));
            $icon = ($style === 'primary' && $isFirstPrimary)
                ? ' <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>'
                : '';
            if ($style === 'primary') {
                $isFirstPrimary = false;
            }
            $parts[] = '<a class="section-hero__button ' . SectionButtonStyle::heroClass($style) . '" href="'
                . htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">'
                . htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . $icon . '</a>';
        }

        return implode("\n", $parts);
    }

    /**
     * @param list<mixed> $buttons
     */
    public static function render(array $buttons): string
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
            $style = SectionButtonStyle::normalize((string) ($button['style'] ?? 'primary'));
            $parts[] = '<a class="section-button ' . SectionButtonStyle::sectionClass($style) . '" href="'
                . htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">'
                . htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</a>';
        }

        return implode("\n", $parts);
    }
}
