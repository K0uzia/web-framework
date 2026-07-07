<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Styles et modificateurs du bloc Hero (personnalisation type Elementor).
 */
final class HeroStyle
{
    /** @var list<string> */
    public const VISUAL_VARIANTS = ['split', 'split-left', 'image-below', 'video'];

    /**
     * @return array<string, string>
     */
    public static function defaults(string $variant): array
    {
        $base = [
            'bg' => 'background',
            'text_align' => 'center',
            'padding' => 'lg',
            'min_height' => 'auto',
            'content_width' => 'narrow',
            'title_size' => 'lg',
            'subtitle_size' => 'md',
            'image_border' => 'none',
            'image_radius' => 'lg',
            'image_shadow' => 'md',
        ];

        return match ($variant) {
            'fullscreen' => array_merge($base, [
                'min_height' => 'viewport',
                'title_size' => 'display',
                'padding' => 'md',
                'bg' => 'primary',
            ]),
            'minimal' => array_merge($base, [
                'subtitle_size' => 'hidden',
                'title_size' => 'xl',
                'padding' => 'xl',
                'content_width' => 'narrow',
            ]),
            'badge' => array_merge($base, [
                'title_size' => 'xl',
                'bg' => 'background',
                'text_align' => 'center',
            ]),
            'split', 'split-left' => array_merge($base, [
                'text_align' => 'left',
                'content_width' => 'wide',
                'image_border' => 'thin',
                'image_radius' => 'lg',
                'image_shadow' => 'md',
            ]),
            'image-below' => array_merge($base, [
                'content_width' => 'default',
                'image_border' => 'thin',
                'image_radius' => 'lg',
                'image_shadow' => 'md',
            ]),
            'video' => array_merge($base, [
                'text_align' => 'left',
                'content_width' => 'wide',
                'image_border' => 'thin',
                'image_radius' => 'md',
            ]),
            'centered' => array_merge($base, [
                'bg' => 'background',
                'padding' => 'lg',
                'min_height' => 'auto',
                'content_width' => 'narrow',
                'title_size' => 'lg',
            ]),
            default => $base,
        };
    }

    /**
     * @param array<string, mixed> $style
     *
     * @return array<string, string>
     */
    public static function resolve(array $style, string $variant): array
    {
        $defaults = self::defaults($variant);
        $resolved = $defaults;
        foreach ($style as $key => $value) {
            if (!is_scalar($value)) {
                continue;
            }
            $str = trim((string) $value);
            if ($str !== '') {
                $resolved[(string) $key] = $str;
            }
        }

        return $resolved;
    }

    /**
     * @param array<string, mixed> $style
     */
    public static function modifierClasses(array $style, string $variant): string
    {
        $s = self::resolve($style, $variant);
        $classes = [];

        $map = [
            'min_height' => ['large' => 'section-hero--min-h-large', 'viewport' => 'section-hero--min-h-viewport'],
            'content_width' => ['narrow' => 'section-hero--width-narrow', 'wide' => 'section-hero--width-wide', 'default' => 'section-hero--width-default'],
            'title_size' => ['sm' => 'section-hero--title-sm', 'md' => 'section-hero--title-md', 'lg' => 'section-hero--title-lg', 'xl' => 'section-hero--title-xl', 'display' => 'section-hero--title-display'],
            'subtitle_size' => ['sm' => 'section-hero--subtitle-sm', 'md' => 'section-hero--subtitle-md', 'lg' => 'section-hero--subtitle-lg', 'hidden' => 'section-hero--subtitle-hidden'],
            'image_border' => ['thin' => 'section-hero--img-border'],
            'image_radius' => ['none' => 'section-hero--img-radius-none', 'md' => 'section-hero--img-radius-md', 'lg' => 'section-hero--img-radius-lg'],
            'image_shadow' => ['none' => 'section-hero--img-shadow-none', 'md' => 'section-hero--img-shadow-md'],
        ];

        foreach ($map as $key => $valueMap) {
            $value = $s[$key] ?? '';
            if (isset($valueMap[$value])) {
                $classes[] = $valueMap[$value];
            }
        }

        return implode(' ', $classes);
    }

    /**
     * @param array<string, mixed> $content
     */
    public static function renderBackdrop(array $content, string $variant): string
    {
        if ($variant !== 'fullscreen') {
            return '';
        }

        $videoUrl = trim((string) ($content['video_url'] ?? ''));
        if ($videoUrl !== '') {
            $video = self::videoBackgroundHtml($videoUrl);
            if ($video !== '') {
                return '<div class="section-hero__backdrop" aria-hidden="true">'
                    . $video
                    . '<span class="section-hero__backdrop-overlay"></span>'
                    . '</div>';
            }
        }

        $imageUrl = StockImages::resolve(
            (string) ($content['image_url'] ?? ''),
            static fn (): string => StockImages::sectionHeroFallback('hero'),
        );
        if ($imageUrl === '') {
            return '';
        }

        return '<div class="section-hero__backdrop" aria-hidden="true">'
            . '<img class="section-hero__backdrop-img" src="' . htmlspecialchars($imageUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            . '" alt="" width="1920" height="1080" loading="eager" decoding="async" />'
            . '<span class="section-hero__backdrop-overlay"></span>'
            . '</div>';
    }

    public static function hasBackdrop(array $content, string $variant): bool
    {
        return self::renderBackdrop($content, $variant) !== '';
    }

    /**
     * @param array<string, mixed> $content
     * @param array<string, mixed> $style
     */
    public static function renderVisual(array $content, array $style, string $variant): string
    {
        $videoUrl = trim((string) ($content['video_url'] ?? ''));
        if ($variant === 'video' && $videoUrl !== '') {
            $embed = self::videoEmbedHtml($videoUrl);
            if ($embed !== '') {
                return $embed;
            }
        }

        $needsImage = in_array($variant, ['split', 'split-left', 'image-below', 'video'], true);
        if (!$needsImage) {
            return '';
        }

        $imageUrl = StockImages::resolve(
            (string) ($content['image_url'] ?? ''),
            static fn (): string => StockImages::sectionHeroFallback('hero'),
        );
        $imageTitle = trim((string) ($content['title'] ?? ''));
        $safeAlt = htmlspecialchars($imageTitle !== '' ? $imageTitle : 'Illustration', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        if ($imageUrl === '') {
            return '<div class="section-hero__visual-placeholder" aria-hidden="true"></div>';
        }

        return '<img class="section-hero__img" src="' . htmlspecialchars($imageUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            . '" alt="' . $safeAlt . '" width="1280" height="800" loading="eager" decoding="async" />';
    }

    public static function videoEmbedHtml(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }

        if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([a-zA-Z0-9_-]{6,})~', $url, $m) === 1) {
            $id = $m[1];

            return '<div class="section-hero__video section-hero__video--embed">'
                . '<iframe class="section-hero__iframe" src="https://www.youtube-nocookie.com/embed/'
                . htmlspecialchars($id, ENT_QUOTES) . '?rel=0&amp;modestbranding=1" title="Vidéo" loading="lazy" '
                . self::iframeReferrerPolicy() . ' allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>';
        }

        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m) === 1) {
            return '<div class="section-hero__video section-hero__video--embed"><iframe class="section-hero__iframe" src="https://player.vimeo.com/video/'
                . htmlspecialchars($m[1], ENT_QUOTES) . '?title=0&amp;byline=0&amp;portrait=0" title="Vidéo" loading="lazy" '
                . self::iframeReferrerPolicy() . ' allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe></div>';
        }

        if (preg_match('~\.(mp4|webm|ogg)(\?.*)?$~i', $url) === 1) {
            return '<div class="section-hero__video section-hero__video--embed"><video class="section-hero__video-file" src="'
                . htmlspecialchars($url, ENT_QUOTES) . '" controls playsinline preload="metadata"></video></div>';
        }

        return '';
    }

    public static function videoBackgroundHtml(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }

        if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([a-zA-Z0-9_-]{6,})~', $url, $m) === 1) {
            $id = htmlspecialchars($m[1], ENT_QUOTES);
            $params = 'autoplay=1&amp;mute=1&amp;controls=0&amp;loop=1&amp;playlist=' . $id
                . '&amp;playsinline=1&amp;rel=0&amp;modestbranding=1&amp;showinfo=0';

            return '<iframe class="section-hero__backdrop-iframe" src="https://www.youtube-nocookie.com/embed/'
                . $id . '?' . $params . '" title="" tabindex="-1" loading="lazy" '
                . self::iframeReferrerPolicy() . ' allow="autoplay; encrypted-media; picture-in-picture"></iframe>';
        }

        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m) === 1) {
            return '<iframe class="section-hero__backdrop-iframe" src="https://player.vimeo.com/video/'
                . htmlspecialchars($m[1], ENT_QUOTES) . '?background=1&amp;autoplay=1&amp;loop=1&amp;muted=1&amp;title=0&amp;byline=0&amp;portrait=0" title="" tabindex="-1" loading="lazy" '
                . self::iframeReferrerPolicy() . ' allow="autoplay; fullscreen; picture-in-picture"></iframe>';
        }

        if (preg_match('~\.(mp4|webm|ogg)(\?.*)?$~i', $url) === 1) {
            return '<video class="section-hero__backdrop-video" src="'
                . htmlspecialchars($url, ENT_QUOTES) . '" autoplay muted loop playsinline preload="auto"></video>';
        }

        return '';
    }

    public static function fieldAppliesToVariant(array $field, string $variant): bool
    {
        $show = $field['show_for_variants'] ?? null;
        if (is_array($show) && $show !== []) {
            return in_array($variant, $show, true);
        }

        $hide = $field['hide_for_variants'] ?? null;
        if (is_array($hide) && in_array($variant, $hide, true)) {
            return false;
        }

        return true;
    }

    private static function iframeReferrerPolicy(): string
    {
        return 'referrerpolicy="strict-origin-when-cross-origin"';
    }
}
