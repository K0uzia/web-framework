<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Rendu HTML spécifique aux variantes testimonials (conversion des blocs React).
 */
final class TestimonialVariantRenderer
{
    /** @var array<string, int> */
    private const MAX_ITEMS = [
        'testimonial4' => 4,
        'testimonial8' => 9,
        'testimonial9' => 6,
        'testimonial10' => 1,
    ];

    /** @var list<string> */
    private const DEFAULT_AVATARS = [
        'avatars/avatar1.jpg',
        'avatars/avatar2.jpg',
        'avatars/avatar3.jpg',
        'avatars/avatar4.jpg',
        'avatars/avatar5.jpg',
        'avatars-webp/avatar-1.webp',
        'avatars-webp/avatar-3.webp',
        'avatars-webp/avatar-4.webp',
        'avatars-webp/avatar-6.webp',
    ];

    /** @var list<int> */
    private const LINE_CLAMPS = [3, 5, 2, 4, 3, 5, 2, 4, 3];

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    public static function enrich(array $data, array $content, string $variant): array
    {
        $data['testimonial_featured_html'] = '';
        $data['testimonial_cards_html'] = '';
        $data['testimonial_masonry_html'] = '';
        $data['testimonial_single_html'] = '';

        return match ($variant) {
            'testimonial4' => self::enrichTestimonial4($data, $content),
            'testimonial8' => self::enrichTestimonial8($data, $content),
            'testimonial9' => self::enrichTestimonial9($data, $content),
            'testimonial10' => self::enrichTestimonial10($data, $content),
            default => $data,
        };
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichTestimonial4(array $data, array $content): array
    {
        $items = self::items($content, 'testimonial4');
        $featured = $items[0] ?? [];
        $imageUrl = SectionAssets::resolve(
            (string) ($featured['url'] ?? ''),
            SectionAssets::shared('features', 'placeholder-1.svg'),
        );
        $safeImageUrl = htmlspecialchars($imageUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $imageAlt = htmlspecialchars(
            trim((string) ($featured['title'] ?? '')) !== '' ? (string) $featured['title'] : 'Illustration',
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8',
        );

        $data['testimonial_featured_html'] = '<div class="section-testimonials__featured-grid">'
            . '<img class="section-testimonials__featured-image" src="' . $safeImageUrl . '" alt="' . $imageAlt
            . '" width="640" height="288" loading="lazy" decoding="async" />'
            . '<article class="section-testimonials__featured-card">'
            . self::quoteBlock((string) ($featured['text'] ?? ''), 'section-testimonials__quote--featured')
            . self::authorBlock($featured, 0, false)
            . '</article>'
            . '</div>';

        $cards = '';
        foreach (array_slice($items, 1) as $index => $item) {
            $cards .= self::renderCompactCard($item, $index + 1);
        }
        $data['testimonial_cards_html'] = $cards;

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichTestimonial8(array $data, array $content): array
    {
        $html = '';
        foreach (self::items($content, 'testimonial8') as $index => $item) {
            $displayIdx = ($index % 3) * 3 + intdiv($index, 3);
            $visibility = '';
            if ($displayIdx > 3 && $displayIdx <= 5) {
                $visibility = ' section-testimonials__masonry-item--from-md';
            } elseif ($displayIdx > 5) {
                $visibility = ' section-testimonials__masonry-item--from-lg';
            }
            $clamp = self::LINE_CLAMPS[$index] ?? 3;
            $html .= '<div class="section-testimonials__masonry-item' . $visibility . '">'
                . self::renderMasonryCard($item, $index, $clamp, false)
                . '</div>';
        }
        $data['testimonial_masonry_html'] = $html;

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichTestimonial9(array $data, array $content): array
    {
        $html = '';
        foreach (self::items($content, 'testimonial9') as $index => $item) {
            $html .= self::renderMasonryCard($item, $index, 0, true);
        }
        $data['testimonial_masonry_html'] = $html;

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichTestimonial10(array $data, array $content): array
    {
        $quote = trim((string) ($content['quote'] ?? ''));
        if ($quote === '') {
            $items = self::items($content, 'testimonial10');
            $quote = trim((string) ($items[0]['text'] ?? ''));
        }

        $name = trim((string) ($content['author_name'] ?? ''));
        $role = trim((string) ($content['author_role'] ?? ''));
        $avatar = trim((string) ($content['author_avatar'] ?? ''));
        if ($name === '') {
            $items = self::items($content, 'testimonial10');
            $name = trim((string) ($items[0]['title'] ?? ''));
            $role = trim((string) ($items[0]['label'] ?? ''));
            $avatar = trim((string) ($items[0]['url'] ?? ''));
        }

        $safeQuote = htmlspecialchars($quote, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $avatarUrl = self::avatarUrl($avatar, 0);
        $safeAvatarUrl = htmlspecialchars($avatarUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeName = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeRole = htmlspecialchars($role, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeAlt = htmlspecialchars($name !== '' ? $name : 'Auteur', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $data['testimonial_single_html'] = '<blockquote class="section-testimonials__single-quote">'
            . '<p>&ldquo;' . $safeQuote . '&rdquo;</p>'
            . '</blockquote>'
            . '<div class="section-testimonials__single-author">'
            . '<img class="section-testimonials__single-avatar" src="' . $safeAvatarUrl . '" alt="' . $safeAlt
            . '" width="64" height="64" loading="lazy" decoding="async" />'
            . '<div class="section-testimonials__single-meta">'
            . '<p class="section-testimonials__single-name">' . $safeName . '</p>'
            . '<p class="section-testimonials__single-role">' . $safeRole . '</p>'
            . '</div>'
            . '</div>';

        return $data;
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function renderCompactCard(array $item, int $index): string
    {
        return '<article class="section-testimonials__card">'
            . '<div class="section-testimonials__card-body">'
            . self::quoteBlock((string) ($item['text'] ?? ''))
            . '</div>'
            . '<footer class="section-testimonials__card-footer">'
            . self::authorBlock($item, $index, true)
            . '</footer>'
            . '</article>';
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function renderMasonryCard(array $item, int $index, int $clamp, bool $withSocial): string
    {
        $clampClass = $clamp > 0 ? ' section-testimonials__quote--clamp-' . $clamp : '';
        $social = '';
        if ($withSocial) {
            $icon = trim((string) ($item['icon'] ?? ''));
            $href = trim((string) ($item['href'] ?? '#'));
            if ($icon !== '') {
                $social = self::socialLinkHtml($icon, $href);
            }
        }

        return '<article class="section-testimonials__masonry-card">'
            . '<div class="section-testimonials__masonry-head">'
            . '<div class="section-testimonials__masonry-author">'
            . self::avatarImg($item, $index, $withSocial ? 36 : 40)
            . self::authorMeta($item)
            . '</div>'
            . $social
            . '</div>'
            . self::quoteBlock((string) ($item['text'] ?? ''), 'section-testimonials__quote--muted' . $clampClass)
            . '</article>';
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function authorBlock(array $item, int $index, bool $withAvatar): string
    {
        $html = '<div class="section-testimonials__author">';
        if ($withAvatar) {
            $html .= self::avatarImg($item, $index, 36);
        }
        $html .= self::authorMeta($item) . '</div>';

        return $html;
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function authorMeta(array $item): string
    {
        $name = htmlspecialchars(trim((string) ($item['title'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $role = htmlspecialchars(trim((string) ($item['label'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<div class="section-testimonials__meta">'
            . '<p class="section-testimonials__name">' . $name . '</p>'
            . '<p class="section-testimonials__role">' . $role . '</p>'
            . '</div>';
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function avatarImg(array $item, int $index, int $size): string
    {
        $name = trim((string) ($item['title'] ?? ''));
        $url = self::avatarUrl((string) ($item['url'] ?? ''), $index);
        $safeUrl = htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeAlt = htmlspecialchars($name !== '' ? $name : 'Auteur', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<img class="section-testimonials__avatar" src="' . $safeUrl . '" alt="' . $safeAlt
            . '" width="' . $size . '" height="' . $size . '" loading="lazy" decoding="async" />';
    }

    private static function quoteBlock(string $text, string $extraClass = ''): string
    {
        $safe = htmlspecialchars(trim($text), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $class = 'section-testimonials__quote' . ($extraClass !== '' ? ' ' . $extraClass : '');

        return '<blockquote class="' . $class . '"><q>' . $safe . '</q></blockquote>';
    }

    private static function socialLinkHtml(string $icon, string $href): string
    {
        $faClass = match (strtolower($icon)) {
            'linkedin' => 'fa-brands fa-linkedin',
            'instagram' => 'fa-brands fa-instagram',
            'facebook' => 'fa-brands fa-facebook-f',
            default => 'fa-brands fa-x-twitter',
        };
        $safeHref = htmlspecialchars($href !== '' ? $href : '#', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $label = match (strtolower($icon)) {
            'linkedin' => 'LinkedIn',
            'instagram' => 'Instagram',
            'facebook' => 'Facebook',
            default => 'X',
        };
        $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<a class="section-testimonials__social-link" href="' . $safeHref
            . '" target="_blank" rel="noopener noreferrer" aria-label="' . $safeLabel . '">'
            . '<i class="' . $faClass . '" aria-hidden="true"></i></a>';
    }

    private static function avatarUrl(string $url, int $index): string
    {
        $fallbackFile = self::DEFAULT_AVATARS[$index % count(self::DEFAULT_AVATARS)];

        return SectionAssets::resolve(
            $url,
            SectionAssets::shared('hero', $fallbackFile),
        );
    }

    /**
     * @param array<string, mixed> $content
     *
     * @return list<array<string, mixed>>
     */
    private static function items(array $content, string $variant): array
    {
        $raw = is_array($content['items'] ?? null) ? $content['items'] : [];
        $max = self::MAX_ITEMS[$variant] ?? 4;
        $items = [];
        foreach (array_slice($raw, 0, $max) as $item) {
            if (is_array($item)) {
                $items[] = $item;
            }
        }

        return $items;
    }
}
