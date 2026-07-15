<?php

declare(strict_types=1);

namespace Capsule\Section\Community;

use Capsule\SectionAssets;

/**
 * Rendu HTML spécifique aux variantes community (conversion des blocs React).
 */
final class CommunityVariantRenderer
{
    /** @var array<string, int> */
    private const MAX_ITEMS = [
        'community1' => 8,
        'community2' => 8,
    ];

    /** @var array<string, string> */
    private const ICON_MAP = [
        'instagram' => 'fa-brands fa-instagram',
        'facebook' => 'fa-brands fa-facebook-f',
        'linkedin' => 'fa-brands fa-linkedin',
        'github' => 'fa-brands fa-github',
        'discord' => 'fa-brands fa-discord',
        'x' => 'fa-brands fa-x-twitter',
        'twitter' => 'fa-brands fa-x-twitter',
    ];

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    public static function enrich(array $data, array $content, string $variant): array
    {
        $data['community_logo_html'] = $variant === 'community1' ? self::logoHtml($content) : '';
        $data['community_social_html'] = match ($variant) {
            'community1' => self::socialButtonsHtml($content),
            'community2' => self::socialCardsHtml($content),
            default => '',
        };

        return $data;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function logoHtml(array $content): string
    {
        $url = SectionAssets::resolve(
            (string) ($content['image_url'] ?? ''),
            SectionAssets::shared('hero', 'block-1.svg'),
        );
        $alt = trim((string) ($content['image_alt'] ?? $content['title'] ?? 'Logo'));
        $safeUrl = htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeAlt = htmlspecialchars($alt !== '' ? $alt : 'Logo', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<img class="section-community__logo--community1" src="' . $safeUrl . '" alt="' . $safeAlt
            . '" width="40" height="40" loading="lazy" decoding="async" />';
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function socialButtonsHtml(array $content): string
    {
        $html = '';
        foreach (self::items($content, 'community1') as $item) {
            $href = trim((string) ($item['href'] ?? ''));
            if ($href === '') {
                continue;
            }
            $label = trim((string) ($item['title'] ?? $item['label'] ?? 'Réseau social'));
            $iconClass = self::iconClass((string) ($item['icon'] ?? ''));
            $safeHref = htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeIcon = htmlspecialchars($iconClass, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            $html .= '<a class="section-community__social-button--community1" href="' . $safeHref . '"'
                . ' target="_blank" rel="noopener noreferrer"'
                . ' aria-label="' . $safeLabel . ' (ouvre un nouvel onglet)">'
                . '<i class="' . $safeIcon . '" aria-hidden="true"></i></a>';
        }

        return $html;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function socialCardsHtml(array $content): string
    {
        $html = '';
        foreach (self::items($content, 'community2') as $item) {
            $href = trim((string) ($item['href'] ?? ''));
            $title = trim((string) ($item['title'] ?? ''));
            if ($href === '' || $title === '') {
                continue;
            }
            $description = trim((string) ($item['text'] ?? ''));
            $iconClass = self::iconClass((string) ($item['icon'] ?? ''));
            $safeHref = htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeDescription = htmlspecialchars($description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeIcon = htmlspecialchars($iconClass, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            $html .= '<a class="section-community__card--community2" href="' . $safeHref . '">'
                . '<div class="section-community__card-head--community2">'
                . '<i class="' . $safeIcon . ' section-community__card-icon--community2" aria-hidden="true"></i>'
                . '<i class="fa-solid fa-arrow-up-right section-community__card-arrow--community2" aria-hidden="true"></i>'
                . '</div>'
                . '<div class="section-community__card-body--community2">'
                . '<h3 class="section-community__card-title--community2">' . $safeTitle . '</h3>';
            if ($description !== '') {
                $html .= '<p class="section-community__card-text--community2">' . $safeDescription . '</p>';
            }
            $html .= '</div></a>';
        }

        return $html;
    }

    private static function iconClass(string $raw): string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return 'fa-brands fa-x-twitter';
        }

        $key = strtolower($raw);
        if (isset(self::ICON_MAP[$key])) {
            return self::ICON_MAP[$key];
        }

        if (str_contains($raw, 'fa-')) {
            return $raw;
        }

        return 'fa-brands fa-' . ltrim($key, '-');
    }

    /**
     * @param array<string, mixed> $content
     *
     * @return list<array<string, mixed>>
     */
    private static function items(array $content, string $variant): array
    {
        $raw = is_array($content['items'] ?? null) ? $content['items'] : [];
        $max = self::MAX_ITEMS[$variant] ?? 8;

        return array_slice(array_values(array_filter($raw, 'is_array')), 0, $max);
    }
}
