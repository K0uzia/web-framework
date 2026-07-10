<?php

declare(strict_types=1);

namespace Capsule;

use Capsule\Section\SectionItemsTrait;

/**
 * Rendu HTML spécifique aux variantes process (conversion des blocs React).
 */
final class ProcessVariantRenderer
{
    use SectionItemsTrait;

    /** @var array<string, int> */
    private const MAX_ITEMS = [
        'process1' => 8,
    ];

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    public static function enrich(array $data, array $content, string $variant): array
    {
        $data['process_steps_html'] = match ($variant) {
            default => self::stepsProcess1Html($content),
        };
        $data['process_cta_html'] = self::ctaHtml($content);

        return $data;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function stepsProcess1Html(array $content): string
    {
        $html = '';
        $index = 0;
        foreach (self::itemsFromContent($content, self::MAX_ITEMS['process1']) as $item) {
            $title = trim((string) ($item['title'] ?? ''));
            if ($title === '') {
                continue;
            }

            $description = trim((string) ($item['text'] ?? ''));
            $stepLabel = trim((string) ($item['label'] ?? ''));
            if ($stepLabel === '') {
                $stepLabel = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
            }

            $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeDescription = htmlspecialchars($description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeStep = htmlspecialchars($stepLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            $html .= '<li class="section-process__step--process1">'
                . self::cornerMarkSvg()
                . '<div class="section-process__step-index--process1" aria-hidden="true">' . $safeStep . '</div>'
                . '<div class="section-process__step-body--process1">'
                . '<h3 class="section-process__step-title--process1">' . $safeTitle . '</h3>'
                . ($description !== ''
                    ? '<p class="section-process__step-text--process1">' . $safeDescription . '</p>'
                    : '')
                . '</div>'
                . '</li>';

            $index++;
        }

        return $html;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function ctaHtml(array $content): string
    {
        $buttons = $content['buttons'] ?? null;
        if (is_array($buttons) && $buttons !== []) {
            $button = $buttons[0];
            if (is_array($button)) {
                $label = trim((string) ($button['label'] ?? ''));
                $href = trim((string) ($button['href'] ?? ''));
                if ($label !== '' && $href !== '') {
                    return self::ctaLink($label, $href);
                }
            }
        }

        $label = trim((string) ($content['cta_label'] ?? $content['button_label'] ?? ''));
        $href = trim((string) ($content['cta_href'] ?? $content['button_href'] ?? ''));
        if ($label === '' || $href === '') {
            return '';
        }

        return self::ctaLink($label, $href);
    }

    private static function ctaLink(string $label, string $href): string
    {
        $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeHref = htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<a class="section-process__cta--process1" href="' . $safeHref . '">'
            . '<i class="fa-solid fa-arrow-turn-down section-process__cta-icon--process1" aria-hidden="true"></i>'
            . $safeLabel
            . '</a>';
    }

    private static function cornerMarkSvg(): string
    {
        return '<svg class="section-process__mark--process1" width="22" height="20" viewBox="0 0 22 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">'
            . '<line x1="0.607422" y1="2.57422" x2="21.5762" y2="2.57422" stroke="currentColor" stroke-width="4" />'
            . '<line x1="19.5762" y1="19.624" x2="19.5762" y2="4.57422" stroke="currentColor" stroke-width="4" />'
            . '</svg>';
    }
}
