<?php

declare(strict_types=1);

namespace Capsule;

use Capsule\Support\Utf8;

/**
 * Rendu HTML spécifique aux variantes team (conversion des blocs React).
 */
final class TeamVariantRenderer
{
    /** @var array<string, int> */
    private const MAX_ITEMS = [
        'team1' => 12,
        'team2' => 12,
    ];

    /** @var list<string> */
    private const DEFAULT_AVATARS = [
        'avatars-webp/avatar-1.webp',
        'avatars-webp/avatar-3.webp',
        'avatars-webp/avatar-4.webp',
        'avatars-webp/avatar-6.webp',
        'avatars/avatar1.jpg',
        'avatars/avatar2.jpg',
        'avatars/avatar3.jpg',
        'avatars/avatar4.jpg',
        'avatars/avatar5.jpg',
    ];

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    public static function enrich(array $data, array $content, string $variant): array
    {
        $items = self::items($content, $variant);
        $html = '';
        foreach ($items as $index => $item) {
            $name = trim((string) ($item['title'] ?? ''));
            if ($name === '') {
                continue;
            }
            $html .= match ($variant) {
                'team2' => self::memberTeam2Html($item, $index),
                default => self::memberTeam1Html($item, $index),
            };
        }
        $data['team_members_html'] = $html;

        return $data;
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function memberTeam1Html(array $item, int $index): string
    {
        $name = self::text($item, 'title');
        $role = self::text($item, 'label');
        $avatar = self::avatarHtml($item, $index, 'section-team__avatar--team1');

        return '<article class="section-team__member section-team__member--team1">'
            . $avatar
            . '<p class="section-team__name section-team__name--team1">' . $name . '</p>'
            . ($role !== '' ? '<p class="section-team__role section-team__role--team1">' . $role . '</p>' : '')
            . '</article>';
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function memberTeam2Html(array $item, int $index): string
    {
        $name = self::text($item, 'title');
        $role = self::text($item, 'label');
        $avatar = self::avatarHtml($item, $index, 'section-team__avatar--team2');
        $social = self::socialLinksHtml($item);

        return '<article class="section-team__member section-team__member--team2">'
            . '<div class="section-team__member-inner section-team__member-inner--team2">'
            . $avatar
            . '<div class="section-team__meta section-team__meta--team2">'
            . '<h3 class="section-team__name section-team__name--team2">' . $name . '</h3>'
            . ($role !== '' ? '<p class="section-team__role section-team__role--team2">' . $role . '</p>' : '')
            . '</div>'
            . $social
            . '</div>'
            . '</article>';
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function avatarHtml(array $item, int $index, string $class): string
    {
        $name = trim((string) ($item['title'] ?? ''));
        $url = self::avatarUrl((string) ($item['url'] ?? ''), $index);
        $safeUrl = htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeAlt = htmlspecialchars($name !== '' ? $name : 'Membre de l\'équipe', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $initials = htmlspecialchars(self::initials($name), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<div class="section-team__avatar ' . $class . '">'
            . '<img class="section-team__avatar-img" src="' . $safeUrl . '" alt="' . $safeAlt
            . '" width="96" height="96" loading="lazy" decoding="async" />'
            . '<span class="section-team__avatar-fallback" aria-hidden="true">' . $initials . '</span>'
            . '</div>';
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function socialLinksHtml(array $item): string
    {
        $links = [
            'github' => ['fa-brands fa-github', 'GitHub'],
            'twitter' => ['fa-brands fa-x-twitter', 'X'],
            'linkedin' => ['fa-brands fa-linkedin', 'LinkedIn'],
        ];
        $html = '';
        foreach ($links as $key => [$iconClass, $label]) {
            $href = trim((string) ($item[$key] ?? ''));
            if ($href === '') {
                continue;
            }
            $safeHref = htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $html .= '<a class="section-team__social-link" href="' . $safeHref . '" target="_blank" rel="noopener noreferrer"'
                . ' aria-label="' . $safeLabel . ' (ouvre un nouvel onglet)">'
                . '<i class="' . $iconClass . '" aria-hidden="true"></i></a>';
        }
        if ($html === '') {
            return '';
        }

        return '<div class="section-team__social">' . $html . '</div>';
    }

    /**
     * @param array<string, mixed> $content
     *
     * @return list<array<string, mixed>>
     */
    private static function items(array $content, string $variant): array
    {
        $raw = is_array($content['items'] ?? null) ? $content['items'] : [];
        $max = self::MAX_ITEMS[$variant] ?? 12;
        $items = [];
        foreach (array_slice($raw, 0, $max) as $item) {
            if (is_array($item)) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function text(array $item, string $key): string
    {
        $value = trim((string) ($item[$key] ?? ''));
        if ($value === '') {
            return '';
        }

        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private static function avatarUrl(string $url, int $index): string
    {
        $fallbackFile = self::DEFAULT_AVATARS[$index % count(self::DEFAULT_AVATARS)];

        return SectionAssets::resolve(
            $url,
            SectionAssets::shared('hero', $fallbackFile),
        );
    }

    private static function initials(string $name): string
    {
        $words = preg_split('/\s+/', $name) ?: [];
        $initials = '';
        foreach (array_slice(array_filter($words), 0, 2) as $word) {
            $initials .= Utf8::strtoupper(Utf8::substr($word, 0, 1));
        }

        return $initials !== '' ? $initials : '?';
    }
}
