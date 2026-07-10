<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Rendu HTML spécifique aux variantes careers (conversion des blocs React).
 */
final class CareersVariantRenderer
{
    /** @var array<string, int> */
    private const MAX_ITEMS = [
        'careers1' => 24,
        'careers4' => 24,
    ];

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    public static function enrich(array $data, array $content, string $variant): array
    {
        $data['careers_groups_html'] = match ($variant) {
            'careers4' => self::groupsCareers4Html($content),
            default => self::groupsCareers1Html($content),
        };

        return $data;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function groupsCareers1Html(array $content): string
    {
        $html = '';
        foreach (self::groupedItems($content, 'careers1') as $group => $roles) {
            $safeGroup = htmlspecialchars($group, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $html .= '<div class="section-careers__department--careers1">'
                . '<h3 class="section-careers__department-title--careers1">' . $safeGroup . '</h3>'
                . '<ul class="section-careers__roles--careers1">';
            foreach ($roles as $role) {
                $html .= self::roleCareers1Html($role);
            }
            $html .= '</ul></div>';
        }

        return $html;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function groupsCareers4Html(array $content): string
    {
        $html = '';
        foreach (self::groupedItems($content, 'careers4') as $group => $roles) {
            $safeGroup = htmlspecialchars($group, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $html .= '<div class="section-careers__category--careers4">'
                . '<h3 class="section-careers__category-title--careers4">' . $safeGroup . '</h3>';
            foreach ($roles as $role) {
                $html .= self::roleCareers4Html($role);
            }
            $html .= '</div>';
        }

        return $html;
    }

    /**
     * @param array<string, mixed> $role
     */
    private static function roleCareers1Html(array $role): string
    {
        $title = trim((string) ($role['title'] ?? ''));
        $location = trim((string) ($role['label'] ?? ''));
        $href = trim((string) ($role['href'] ?? ''));
        if ($title === '' || $href === '') {
            return '';
        }
        $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeLocation = htmlspecialchars($location, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeHref = htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $locationHtml = $location !== ''
            ? '<span class="section-careers__role-location--careers1">' . $safeLocation . '</span>'
            : '';

        return '<li class="section-careers__role--careers1">'
            . '<a class="section-careers__role-link--careers1" href="' . $safeHref . '">'
            . '<span class="section-careers__role-main--careers1">'
            . '<span class="section-careers__role-title--careers1">' . $safeTitle . '</span>'
            . $locationHtml
            . '</span>'
            . '<i class="fa-solid fa-arrow-right section-careers__role-arrow--careers1" aria-hidden="true"></i>'
            . '</a></li>';
    }

    /**
     * @param array<string, mixed> $role
     */
    private static function roleCareers4Html(array $role): string
    {
        $title = trim((string) ($role['title'] ?? ''));
        $location = trim((string) ($role['label'] ?? ''));
        $href = trim((string) ($role['href'] ?? ''));
        if ($title === '' || $href === '') {
            return '';
        }
        $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeLocation = htmlspecialchars($location, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeHref = htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $locationHtml = $location !== ''
            ? '<p class="section-careers__opening-location--careers4">' . $safeLocation . '</p>'
            : '';

        return '<div class="section-careers__opening--careers4">'
            . '<div class="section-careers__opening-main--careers4">'
            . '<a class="section-careers__opening-title--careers4" href="' . $safeHref . '">' . $safeTitle . '</a>'
            . $locationHtml
            . '</div>'
            . '<a class="section-careers__opening-arrow-link--careers4" href="' . $safeHref . '"'
            . ' aria-label="' . $safeTitle . '">'
            . '<i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>'
            . '</div>';
    }

    /**
     * @param array<string, mixed> $content
     *
     * @return array<string, list<array<string, mixed>>>
     */
    private static function groupedItems(array $content, string $variant): array
    {
        $groups = [];
        foreach (self::items($content, $variant) as $item) {
            $group = trim((string) ($item['group'] ?? ''));
            if ($group === '') {
                $group = 'Autre';
            }
            if (!isset($groups[$group])) {
                $groups[$group] = [];
            }
            $groups[$group][] = $item;
        }

        return $groups;
    }

    /**
     * @param array<string, mixed> $content
     *
     * @return list<array<string, mixed>>
     */
    private static function items(array $content, string $variant): array
    {
        $raw = is_array($content['items'] ?? null) ? $content['items'] : [];
        $max = self::MAX_ITEMS[$variant] ?? 24;

        return array_slice(array_values(array_filter($raw, 'is_array')), 0, $max);
    }
}
