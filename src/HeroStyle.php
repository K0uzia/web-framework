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
            $video = self::videoBackgroundHtml($videoUrl, $content);
            if ($video !== '') {
                $chromeless = !MediaDisplaySettings::videoFlags($content, 'background')['controls'];
                $backdropClass = 'section-hero__backdrop' . ($chromeless ? ' section-hero__backdrop--chromeless' : '');

                return '<div class="' . $backdropClass . '" aria-hidden="true">'
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
            . '<img class="section-hero__backdrop-img ' . MediaDisplaySettings::imageFitClass($content, 'section-hero__backdrop-img') . '" src="' . htmlspecialchars($imageUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
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
            $embed = self::videoEmbedHtml($videoUrl, $content);
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

        return '<img class="section-hero__img ' . MediaDisplaySettings::imageFitClass($content, 'section-hero__img') . '" src="' . htmlspecialchars($imageUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            . '" alt="' . $safeAlt . '" width="1280" height="800" loading="eager" decoding="async" />';
    }

    /**
     * @param array<string, mixed> $content
     */
    public static function videoEmbedHtml(string $url, array $content = []): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }

        $flags = MediaDisplaySettings::videoFlags($content, 'embed');
        $fit = MediaDisplaySettings::videoFit($content, 'contain');
        $chromeless = !$flags['controls'];

        if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([a-zA-Z0-9_-]{6,})~', $url, $m) === 1) {
            $id = $m[1];
            $params = self::youtubeParams($id, $flags, embed: true);
            $embedUrl = 'https://www.youtube-nocookie.com/embed/' . $id . '?' . implode('&', $params);
            $iframe = self::videoIframeTag(
                'section-hero__iframe' . ($chromeless ? ' section-hero__iframe--no-controls' : ''),
                $embedUrl,
                $chromeless,
                'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture',
                $flags['controls'],
            );

            return self::videoEmbedShell($iframe, $fit, $chromeless);
        }

        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m) === 1) {
            $params = self::vimeoParams($flags, embed: true);
            $embedUrl = 'https://player.vimeo.com/video/' . $m[1] . '?' . implode('&', $params);
            $iframe = self::videoIframeTag(
                'section-hero__iframe' . ($chromeless ? ' section-hero__iframe--no-controls' : ''),
                $embedUrl,
                $chromeless,
                'autoplay; fullscreen; picture-in-picture',
                $flags['controls'],
            );

            return self::videoEmbedShell($iframe, $fit, $chromeless);
        }

        if (preg_match('~\.(mp4|webm|ogg)(\?.*)?$~i', $url) === 1) {
            $fileClass = MediaDisplaySettings::videoFitClass($content, 'section-hero__video-file', 'contain');
            $attrs = ['class="section-hero__video-file ' . $fileClass . '"', 'src="' . htmlspecialchars($url, ENT_QUOTES) . '"', 'playsinline', 'preload="metadata"'];
            if ($flags['autoplay']) {
                $attrs[] = 'autoplay';
            }
            if ($flags['muted']) {
                $attrs[] = 'muted';
            }
            if ($flags['loop']) {
                $attrs[] = 'loop';
            }
            if ($flags['controls']) {
                $attrs[] = 'controls';
            }

            return '<div class="section-hero__video section-hero__video--embed section-hero__video--file"><video ' . implode(' ', $attrs) . '></video></div>';
        }

        return '';
    }

    /**
     * @param array<string, mixed> $content
     */
    public static function videoBackgroundHtml(string $url, array $content = []): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }

        $flags = MediaDisplaySettings::videoFlags($content, 'background');
        $fit = MediaDisplaySettings::videoFit($content, 'cover');
        $chromeless = !$flags['controls'];

        if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([a-zA-Z0-9_-]{6,})~', $url, $m) === 1) {
            $params = self::youtubeParams($m[1], $flags, embed: false);
            $embedUrl = 'https://www.youtube-nocookie.com/embed/' . $m[1] . '?' . implode('&', $params);
            $iframe = self::videoIframeTag(
                'section-hero__backdrop-iframe' . ($chromeless ? ' section-hero__iframe--no-controls' : ''),
                $embedUrl,
                $chromeless,
                'autoplay; encrypted-media; picture-in-picture',
                false,
                backdrop: true,
            );

            return self::videoBackdropShell($iframe, $fit, $chromeless);
        }

        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m) === 1) {
            $params = self::vimeoParams($flags, embed: false);
            $embedUrl = 'https://player.vimeo.com/video/' . $m[1] . '?' . implode('&', $params);
            $iframe = self::videoIframeTag(
                'section-hero__backdrop-iframe' . ($chromeless ? ' section-hero__iframe--no-controls' : ''),
                $embedUrl,
                $chromeless,
                'autoplay; fullscreen; picture-in-picture',
                false,
                backdrop: true,
            );

            return self::videoBackdropShell($iframe, $fit, $chromeless);
        }

        if (preg_match('~\.(mp4|webm|ogg)(\?.*)?$~i', $url) === 1) {
            $fileClass = MediaDisplaySettings::videoFitClass($content, 'section-hero__backdrop-video', 'cover');
            $attrs = ['class="section-hero__backdrop-video ' . $fileClass . '"', 'src="' . htmlspecialchars($url, ENT_QUOTES) . '"', 'playsinline', 'preload="auto"'];
            if ($flags['autoplay']) {
                $attrs[] = 'autoplay';
            }
            if ($flags['muted']) {
                $attrs[] = 'muted';
            }
            if ($flags['loop']) {
                $attrs[] = 'loop';
            }
            if ($flags['controls']) {
                $attrs[] = 'controls';
            }

            return '<video ' . implode(' ', $attrs) . '></video>';
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

    private static function youtubeOrigin(): string
    {
        $appUrl = $_ENV['APP_URL'] ?? getenv('APP_URL');
        if (is_string($appUrl) && $appUrl !== '') {
            return rtrim($appUrl, '/');
        }

        $host = $_SERVER['HTTP_HOST'] ?? '';
        if (!is_string($host) || $host === '') {
            return '';
        }

        $https = $_SERVER['HTTPS'] ?? '';
        $scheme = ($https !== '' && $https !== 'off') ? 'https' : 'http';

        return $scheme . '://' . $host;
    }

    private static function videoFrameClass(string $fit, bool $chromeless): string
    {
        $class = 'section-hero__video-frame section-hero__video-frame--' . $fit;
        if ($chromeless) {
            $class .= ' section-hero__video-frame--chromeless';
        }

        return $class;
    }

    private static function videoEmbedShell(string $iframe, string $fit, bool $chromeless): string
    {
        $shellClass = 'section-hero__video section-hero__video--embed';
        if ($chromeless) {
            $shellClass .= ' section-hero__video--no-controls';
        }
        $attrs = ' data-hero-video' . ($chromeless ? ' data-hero-video-chromeless="1"' : '');
        $mask = $chromeless ? '<span class="section-hero__video-chrome-mask" aria-hidden="true"></span>' : '';

        return '<div class="' . $shellClass . '"' . $attrs . '><div class="' . self::videoFrameClass($fit, $chromeless) . '">' . self::videoFrameInner($iframe, $chromeless) . '</div>' . $mask . '</div>';
    }

    private static function videoFrameInner(string $iframe, bool $chromeless): string
    {
        if (!$chromeless) {
            return $iframe;
        }

        return '<div class="section-hero__video-frame-zoom">' . $iframe . '</div>';
    }

    private static function videoBackdropShell(string $iframe, string $fit, bool $chromeless): string
    {
        $attrs = ' data-hero-video' . ($chromeless ? ' data-hero-video-chromeless="1"' : '');
        $mask = $chromeless ? '<span class="section-hero__video-chrome-mask section-hero__video-chrome-mask--backdrop" aria-hidden="true"></span>' : '';

        return '<div class="' . self::videoFrameClass($fit, $chromeless) . ' section-hero__video-frame--backdrop"' . $attrs . '>' . self::videoFrameInner($iframe, $chromeless) . $mask . '</div>';
    }

    private static function videoIframeTag(
        string $class,
        string $embedUrl,
        bool $deferred,
        string $allow,
        bool $allowFullscreen = false,
        bool $backdrop = false,
    ): string {
        $safeUrl = htmlspecialchars($embedUrl, ENT_QUOTES);
        $srcAttr = $deferred
            ? 'data-src="' . $safeUrl . '"'
            : 'src="' . $safeUrl . '"';
        $titleAttr = $backdrop ? ' title="" tabindex="-1"' : ' title="Vidéo"';

        return '<iframe class="' . $class . '" ' . $srcAttr . $titleAttr . ' '
            . self::iframeReferrerPolicy()
            . ($allowFullscreen ? ' allowfullscreen' : '')
            . ' allow="' . $allow . '"></iframe>';
    }

    /**
     * @param array{autoplay: bool, muted: bool, loop: bool, controls: bool} $flags
     *
     * @return list<string>
     */
    private static function youtubeParams(string $id, array $flags, bool $embed): array
    {
        $params = ['playsinline=1', 'rel=0', 'modestbranding=1', 'iv_load_policy=3', 'disablekb=1'];
        $autoplay = $flags['autoplay'];
        $muted = $flags['muted'];
        if (!$flags['controls']) {
            $params[] = 'controls=0';
            $params[] = 'cc_load_policy=0';
            $params[] = 'enablejsapi=1';
            $origin = self::youtubeOrigin();
            if ($origin !== '') {
                $params[] = 'origin=' . rawurlencode($origin);
            }
            $autoplay = true;
            $muted = true;
        }
        if ($autoplay) {
            $params[] = 'autoplay=1';
        }
        if ($muted) {
            $params[] = 'mute=1';
        }
        if ($flags['loop']) {
            $params[] = 'loop=1';
            $params[] = 'playlist=' . $id;
        }
        if (!$embed) {
            $params[] = 'fs=0';
        }

        return $params;
    }

    /**
     * @param array{autoplay: bool, muted: bool, loop: bool, controls: bool} $flags
     *
     * @return list<string>
     */
    private static function vimeoParams(array $flags, bool $embed): array
    {
        $params = ['title=0', 'byline=0', 'portrait=0'];
        $autoplay = $flags['autoplay'];
        $muted = $flags['muted'];
        if (!$flags['controls']) {
            $params[] = 'controls=0';
            $params[] = 'cc_load_policy=0';
            $autoplay = true;
            $muted = true;
        }
        if (!$embed) {
            $params[] = 'background=1';
        }
        if ($autoplay) {
            $params[] = 'autoplay=1';
        }
        if ($muted) {
            $params[] = 'muted=1';
        }
        if ($flags['loop']) {
            $params[] = 'loop=1';
        }

        return $params;
    }
}
