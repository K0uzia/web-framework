<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Styles et modificateurs du bloc Hero (personnalisation type Elementor).
 */
final class HeroStyle
{
    /** @var list<string> */
    public const VISUAL_VARIANTS = [
        'hero1', 'hero3', 'hero7', 'hero12', 'hero34', 'hero45', 'hero47',
        'hero67', 'hero78', 'hero115', 'hero195', 'hero206', 'hero243',
    ];

    /** @var array<string, string> Anciens slugs séquentiels (sans ambiguïté) vers slugs extension. */
    public const LEGACY_VARIANT_MAP = [
        'hero2' => 'hero3',
        'hero4' => 'hero12',
        'hero5' => 'hero34',
        'hero6' => 'hero45',
        'hero8' => 'hero67',
        'hero9' => 'hero78',
        'hero10' => 'hero115',
        'hero11' => 'hero195',
        'hero13' => 'hero243',
    ];

    public static function normalizeVariant(string $variant): string
    {
        if (in_array($variant, SectionAssets::heroVariantIds(), true)) {
            return $variant;
        }

        return self::LEGACY_VARIANT_MAP[$variant] ?? $variant;
    }

    /**
     * @return array<string, string>
     */
    public static function defaults(string $variant): array
    {
        return [
            'bg' => 'background',
            'padding' => 'xl',
        ];
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
        return '';
    }

    /**
     * @param array<string, mixed> $content
     */
    public static function renderHeroReviews(array $content): string
    {
        $rating = trim((string) ($content['reviews_rating'] ?? ''));
        $count = trim((string) ($content['reviews_count'] ?? ''));
        $avatars = $content['review_avatars'] ?? [];
        if ($rating === '' && $count === '' && (!is_array($avatars) || $avatars === [])) {
            return '';
        }

        $avatarsHtml = '';
        if (is_array($avatars)) {
            $shown = 0;
            foreach ($avatars as $avatar) {
                if (!is_array($avatar) || $shown >= 5) {
                    continue;
                }
                $url = SectionAssets::resolve(
                    (string) ($avatar['url'] ?? ''),
                    SectionAssets::shared('hero', 'avatars/avatar' . ($shown + 1) . '.jpg'),
                );
                if ($url === '') {
                    continue;
                }
                $alt = htmlspecialchars(trim((string) ($avatar['title'] ?? 'Avatar')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $avatarsHtml .= '<img class="section-hero__avatar" src="' . htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                    . '" alt="' . $alt . '" width="40" height="40" loading="lazy" decoding="async" />';
                $shown++;
            }
        }

        $starsHtml = '';
        for ($i = 0; $i < 5; $i++) {
            $starsHtml .= '<i class="fa-regular fa-star" aria-hidden="true"></i>';
        }

        $ratingValue = $rating !== '' ? htmlspecialchars($rating, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : '5.0';
        $caption = $count !== ''
            ? 'from ' . htmlspecialchars($count, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '+ reviews'
            : '';

        return '<div class="section-hero__reviews">'
            . ($avatarsHtml !== '' ? '<span class="section-hero__avatars">' . $avatarsHtml . '</span>' : '')
            . '<div>'
            . '<div class="section-hero__rating-row" role="img" aria-label="Note ' . $ratingValue . ' sur 5">'
            . $starsHtml
            . '<span class="section-hero__rating-value">' . $ratingValue . '</span>'
            . '</div>'
            . ($caption !== '' ? '<p class="section-hero__reviews-caption">' . $caption . '</p>' : '')
            . '</div>'
            . '</div>';
    }

    /**
     * @param array<string, mixed> $content
     */
    public static function renderHeroVisual(array $content): string
    {
        $imageUrl = SectionAssets::resolve(
            (string) ($content['image_url'] ?? ''),
            '',
        );
        $imageDarkRaw = trim((string) ($content['image_url_dark'] ?? ''));
        $imageDarkUrl = $imageDarkRaw !== ''
            ? SectionAssets::resolve($imageDarkRaw, '')
            : '';
        $altText = trim((string) ($content['image_alt'] ?? $content['title'] ?? 'Hero'));
        $alt = htmlspecialchars($altText !== '' ? $altText : 'Hero', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        if ($imageUrl === '') {
            return '';
        }

        $light = '<img class="section-hero__img section-hero__img--light" src="' . htmlspecialchars($imageUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            . '" alt="' . $alt . '" width="1280" height="720" loading="eager" decoding="async" />';

        if ($imageDarkUrl === '') {
            return $light;
        }

        $dark = '<img class="section-hero__img section-hero__img--dark" src="' . htmlspecialchars($imageDarkUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            . '" alt="' . $alt . '" width="1280" height="720" loading="eager" decoding="async" />';

        return $light . $dark;
    }

    /**
     * @param array<string, mixed> $content
     */
    public static function renderHeroBackdrop(array $content): string
    {
        $imageUrl = SectionAssets::resolve(
            (string) ($content['background_image_url'] ?? ''),
            '',
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

    /**
     * @param array<string, mixed> $content
     */
    public static function renderHeroItems(array $content, string $variant): string
    {
        $items = $content['items'] ?? null;
        if (!is_array($items) || $items === []) {
            return '';
        }

        $parts = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $title = trim((string) ($item['title'] ?? ''));
            $text = trim((string) ($item['text'] ?? ''));
            $imageUrl = SectionAssets::resolve((string) ($item['url'] ?? ''), '');
            $inner = '';
            if ($imageUrl !== '') {
                $alt = htmlspecialchars($title !== '' ? $title : 'Visuel', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $logoClass = str_contains($imageUrl, '/logos/') ? ' section-hero__item-logo' : ' section-hero__item-img';
                $inner .= '<img class="' . trim($logoClass) . '" src="' . htmlspecialchars($imageUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                    . '" alt="' . $alt . '" loading="lazy" decoding="async" />';
            }
            if ($title !== '') {
                $inner .= '<p class="section-hero__item-title">' . htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
            }
            if ($text !== '') {
                $inner .= '<p class="section-hero__item-text">' . htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
            }
            if ($inner === '') {
                continue;
            }
            $parts[] = '<li class="section-hero__item">' . $inner . '</li>';
        }

        if ($parts === []) {
            return '';
        }

        $listClass = 'section-hero__items';
        if (in_array($variant, ['hero195', 'hero206'], true)) {
            $listClass .= ' section-hero__items--row';
        }

        return '<ul class="' . $listClass . '">' . implode('', $parts) . '</ul>';
    }

    /** @deprecated Utiliser renderHeroReviews() */
    public static function renderHero3Reviews(array $content): string
    {
        return self::renderHeroReviews($content);
    }

    /** @deprecated Utiliser renderHeroVisual() */
    public static function renderHero3Visual(array $content): string
    {
        return self::renderHeroVisual($content);
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

        $imageUrl = MediaDisplaySettings::normalizeUrl((string) ($content['image_url'] ?? ''));
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

        $imageUrl = MediaDisplaySettings::normalizeUrl((string) ($content['image_url'] ?? ''));
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
