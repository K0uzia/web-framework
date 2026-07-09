<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Rendu HTML spécifique aux variantes hero (conversion des blocs React).
 */
final class HeroVariantRenderer
{
    /** @var list<string> */
    private const TAB_ICONS = [
        'fa-solid fa-table-columns',
        'fa-solid fa-chart-column',
        'fa-solid fa-chart-pie',
        'fa-solid fa-database',
        'fa-solid fa-layer-group',
    ];

    /** @var list<string> */
    private const FEATURE_ICONS = [
        'fa-solid fa-code',
        'fa-solid fa-microchip',
        'fa-solid fa-keyboard',
        'fa-solid fa-bolt',
        'fa-solid fa-wand-magic-sparkles',
        'fa-solid fa-layer-group',
    ];

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    public static function enrich(array $data, array $content, string $variant, string $buttonsHtml): array
    {
        $data['hero_buttons_html'] = $buttonsHtml;
        $data['hero_reviews_html'] = self::renderReviews($content, $variant === 'hero7');
        $data['hero_visual_html'] = HeroStyle::renderHeroVisual($content);
        $data['hero_backdrop_html'] = '';
        $data['hero_pattern_html'] = '';
        $data['hero_logo_html'] = '';
        $data['hero_logos_html'] = '';
        $data['hero_slider_html'] = '';
        $data['hero_features_html'] = '';
        $data['hero_phone_html'] = '';
        $data['hero_tabs_html'] = '';
        $data['hero_marquee_html'] = '';
        $data['hero_browser_html'] = '';
        $data['hero_rings_html'] = '';
        $data['hero_icon_html'] = '';
        $data['hero_flip_html'] = '';
        $data['hero_banner_html'] = '';
        $data['hero_trust_html'] = '';
        $data['hero_noise_html'] = '';
        $data['hero_items_html'] = '';

        $sectionId = (string) ($data['section_id'] ?? 'hero');

        return match ($variant) {
            'hero7' => self::enrichHero3($data, $content),
            'hero12' => self::enrichHero4($data, $content),
            'hero34' => self::enrichHero5($data, $content),
            'hero45' => self::enrichHero6($data, $content, $sectionId),
            'hero47' => self::enrichHero7($data, $content),
            'hero67' => self::enrichHero8($data, $content),
            'hero78' => self::enrichHero9($data, $content),
            'hero115' => self::enrichHero10($data, $content),
            'hero195' => self::enrichHero11($data, $content, $sectionId),
            'hero206' => self::enrichHero12($data, $content),
            'hero243' => self::enrichHero13($data, $content, $sectionId),
            default => $data,
        };
    }

    /**
     * @param array<string, mixed> $content
     */
    public static function renderReviews(array $content, bool $filledStars = false): string
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
            $sizeClass = $filledStars ? ' section-hero__avatar--lg' : '';
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
                $avatarsHtml .= '<img class="section-hero__avatar' . $sizeClass . '" src="' . htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                    . '" alt="' . $alt . '" width="48" height="48" loading="lazy" decoding="async" />';
                $shown++;
            }
        }

        $starClass = $filledStars ? 'fa-solid fa-star section-hero__star--filled' : 'fa-regular fa-star';
        $starsHtml = str_repeat('<i class="' . $starClass . '" aria-hidden="true"></i>', 5);

        $ratingValue = $rating !== '' ? htmlspecialchars($rating, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : '5.0';
        $caption = $count !== ''
            ? 'sur ' . htmlspecialchars($count, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ' avis et plus'
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
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichHero3(array $data, array $content): array
    {
        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichHero4(array $data, array $content): array
    {
        $pattern = SectionAssets::shared('hero', 'patterns/square-alt-grid.svg');
        $logo = SectionAssets::shared('hero', 'block-1.svg');
        $data['hero_pattern_html'] = '<img class="section-hero__pattern" src="' . htmlspecialchars($pattern, ENT_QUOTES) . '" alt="" aria-hidden="true" width="1200" height="800" decoding="async" />';
        $data['hero_logo_html'] = '<div class="section-hero__logo-card"><img class="section-hero__logo" src="' . htmlspecialchars($logo, ENT_QUOTES) . '" alt="Logo" width="128" height="64" decoding="async" /></div>';

        $logos = [
            ['ui-kit-icon.svg', 'Kit UI'],
            ['typescript-icon.svg', 'TypeScript'],
            ['react-icon.svg', 'React'],
            ['tailwind-icon.svg', 'Tailwind CSS'],
        ];
        $parts = [];
        foreach ($logos as [$file, $alt]) {
            $url = SectionAssets::shared('hero', 'logos/' . $file);
            $parts[] = '<a class="section-hero__tech-logo" href="#" aria-label="' . htmlspecialchars($alt, ENT_QUOTES) . '">'
                . '<img src="' . htmlspecialchars($url, ENT_QUOTES) . '" alt="" width="24" height="24" loading="lazy" decoding="async" />'
                . '</a>';
        }
        $data['hero_logos_html'] = '<div class="section-hero__tech-row">' . implode('', $parts) . '</div>';

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichHero5(array $data, array $content): array
    {
        $badge = trim((string) ($content['badge'] ?? ''));
        if ($badge !== '') {
            $data['hero_badge_html'] = '<p class="section-hero__badge section-hero__badge--plain">' . htmlspecialchars($badge, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichHero6(array $data, array $content, string $sectionId): array
    {
        $items = is_array($content['items'] ?? null) ? $content['items'] : [];
        $slides = '';
        $features = '';
        $index = 0;
        $count = 0;
        foreach (array_slice($items, 0, 3) as $item) {
            if (!is_array($item)) {
                continue;
            }
            $light = SectionAssets::resolve((string) ($item['url'] ?? ''), '');
            $dark = SectionAssets::resolve((string) ($item['href'] ?? $item['image_url_dark'] ?? ''), '');
            $alt = htmlspecialchars(trim((string) ($item['title'] ?? 'Aperçu')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $active = $index === 0 ? ' is-active' : '';
            $img = self::dualImage($light, $dark, $alt, 'section-hero__slide-img');
            $slides .= '<div class="section-hero__slide' . $active . '" data-hero-slide="' . $index . '"'
                . ($index === 0 ? '' : ' aria-hidden="true"') . '>' . $img . '</div>';

            if ($index > 0) {
                $features .= '<span class="section-hero__feature-sep" aria-hidden="true"></span>';
            }

            $icon = self::FEATURE_ICONS[$index % count(self::FEATURE_ICONS)];
            $title = htmlspecialchars(trim((string) ($item['title'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $text = htmlspecialchars(trim((string) ($item['text'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $features .= '<div class="section-hero__feature' . ($index === 0 ? ' is-active' : '') . '" data-hero-slide-target="' . $index . '" tabindex="0">'
                . '<div class="section-hero__feature-icon"><i class="' . $icon . '" aria-hidden="true"></i></div>'
                . '<h3 class="section-hero__feature-title">' . $title . '</h3>'
                . '<p class="section-hero__feature-text">' . $text . '</p>'
                . '</div>';
            $index++;
            $count++;
        }

        $data['hero_slider_html'] = '<div class="section-hero__slider-wrap">'
            . '<div class="section-hero__slider-stage" data-hero-slider id="' . htmlspecialchars($sectionId, ENT_QUOTES) . '-slider">'
            . '<span class="section-hero__slider-dots section-hero__slider-dots--left" aria-hidden="true"></span>'
            . '<span class="section-hero__slider-dots section-hero__slider-dots--right" aria-hidden="true"></span>'
            . '<div class="section-hero__slider-viewport">' . $slides . '</div>'
            . '<span class="section-hero__slider-fade" aria-hidden="true"></span>'
            . '</div></div>';
        $data['hero_features_html'] = '<div class="section-hero__features" data-hero-slider-features>' . $features . '</div>';

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichHero7(array $data, array $content): array
    {
        $screen = SectionAssets::resolve((string) ($content['image_url'] ?? ''), SectionAssets::shared('hero', 'placeholder-dark-7-tall.svg'));
        $frame = SectionAssets::shared('hero', 'mockups/phone-2.png');
        $alt = htmlspecialchars(trim((string) ($content['image_alt'] ?? 'Capture')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $data['hero_phone_html'] = '<div class="section-hero__phone">'
            . '<div class="section-hero__phone-screen"><img src="' . htmlspecialchars($screen, ENT_QUOTES) . '" alt="' . $alt . '" width="320" height="640" loading="lazy" decoding="async" /></div>'
            . '<img class="section-hero__phone-frame" src="' . htmlspecialchars($frame, ENT_QUOTES) . '" alt="" width="450" height="889" loading="lazy" decoding="async" />'
            . '</div>';
        $subheading = trim((string) ($content['subheading'] ?? ''));
        $data['hero_subheading_html'] = $subheading !== ''
            ? '<span class="section-hero__subheading section-hero__subheading--phone">' . htmlspecialchars($subheading, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span>'
            : '';
        $data['hero_buttons_html'] = self::renderPhoneButtons($content);
        $data['hero_visual_html'] = '';

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichHero8(array $data, array $content): array
    {
        $logo = SectionAssets::shared('hero', 'block-1.svg');
        $data['hero_logo_html'] = '<img class="section-hero__logo section-hero__logo--sm" src="' . htmlspecialchars($logo, ENT_QUOTES) . '" alt="Logo" width="112" height="56" decoding="async" />';

        $avatars = [
            'avatar-1.webp', 'avatar-6.webp', 'avatar-3.webp', 'avatar-4.webp',
        ];
        $avatarsHtml = '';
        foreach ($avatars as $i => $file) {
            $url = SectionAssets::shared('hero', 'avatars-webp/' . $file);
            $avatarsHtml .= '<img class="section-hero__avatar section-hero__avatar--sm" src="' . htmlspecialchars($url, ENT_QUOTES) . '" alt="" width="28" height="28" loading="lazy" decoding="async" />';
        }
        $data['hero_trust_html'] = '<div class="section-hero__trust">'
            . '<span class="section-hero__avatars section-hero__avatars--sm">' . $avatarsHtml . '</span>'
            . '<p class="section-hero__trust-caption">Adopté par les leaders du secteur</p>'
            . '</div>';

        $data['hero_buttons_html'] = self::renderBannerButtons($content);

        $banner = SectionAssets::resolve((string) ($content['image_url'] ?? ''), SectionAssets::shared('hero', 'placeholder-1.svg'));
        $data['hero_banner_html'] = '<img class="section-hero__banner" src="' . htmlspecialchars($banner, ENT_QUOTES) . '" alt="" width="1280" height="720" loading="lazy" decoding="async" />';
        $data['hero_visual_html'] = '';

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichHero9(array $data, array $content): array
    {
        $data['hero_backdrop_html'] = HeroStyle::renderHeroBackdrop($content);
        $noise = SectionAssets::shared('hero', 'patterns/noise.png');
        $data['hero_noise_html'] = '<div class="section-hero__noise" style="background-image:url(\'' . htmlspecialchars($noise, ENT_QUOTES) . '\')" aria-hidden="true"></div>';

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichHero10(array $data, array $content): array
    {
        $data['hero_rings_html'] = '<div class="section-hero__rings" aria-hidden="true"><div class="section-hero__rings-inner"><div class="section-hero__rings-core"></div></div></div>';
        $data['hero_icon_html'] = '<span class="section-hero__icon-badge"><i class="fa-solid fa-wifi" aria-hidden="true"></i></span>';
        $data['hero_buttons_html'] = self::renderShowcaseButtons($content);
        $data['hero_visual_html'] = self::renderShowcaseVisual($content);

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichHero11(array $data, array $content, string $sectionId): array
    {
        $items = is_array($content['items'] ?? null) ? $content['items'] : [];
        if ($items === []) {
            return $data;
        }

        $tabs = '';
        $panels = '';
        $index = 0;
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $title = trim((string) ($item['title'] ?? ''));
            if ($title === '') {
                continue;
            }
            $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $icon = self::TAB_ICONS[$index % count(self::TAB_ICONS)];
            $active = $index === 0 ? ' is-active' : '';
            $tabs .= '<button type="button" class="section-hero__tab' . $active . '" role="tab" aria-selected="' . ($index === 0 ? 'true' : 'false') . '" data-hero-tab="' . $index . '">'
                . '<i class="' . $icon . '" aria-hidden="true"></i> ' . $safeTitle
                . '</button>';

            $light = SectionAssets::resolve((string) ($item['url'] ?? ''), '');
            $dark = SectionAssets::resolve((string) ($item['href'] ?? $item['image_url_dark'] ?? ''), '');
            $alt = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $hidden = $index === 0 ? '' : ' hidden';
            $img = self::dualImage($light, $dark, $alt, 'section-hero__tab-img');
            $panels .= '<div class="section-hero__tab-panel' . $active . '" role="tabpanel" data-hero-tab-panel="' . $index . '"' . $hidden . '>'
                . $img
                . '</div>';
            $index++;
        }

        $data['hero_tabs_html'] = '<div class="section-hero__tabs" data-hero-tabs id="' . htmlspecialchars($sectionId, ENT_QUOTES) . '-tabs">'
            . '<div class="section-hero__tablist" role="tablist">' . $tabs . '</div>'
            . '<div class="section-hero__tab-panels">' . $panels . '</div>'
            . '</div>';
        $data['hero_visual_html'] = '';

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichHero12(array $data, array $content): array
    {
        $items = is_array($content['items'] ?? null) ? array_slice($content['items'], 0, 5) : [];
        $logos = '';
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $url = SectionAssets::resolve((string) ($item['url'] ?? ''), '');
            if ($url === '') {
                continue;
            }
            $alt = htmlspecialchars(trim((string) ($item['title'] ?? 'Logo')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $logos .= '<img class="section-hero__marquee-logo" src="' . htmlspecialchars($url, ENT_QUOTES) . '" alt="' . $alt . '" width="120" height="32" loading="lazy" decoding="async" />';
        }
        $data['hero_marquee_html'] = '<div class="section-hero__marquee" data-hero-marquee><div class="section-hero__marquee-track">' . $logos . $logos . '</div></div>'
            . '<div class="section-hero__logos-static">' . $logos . '</div>';

        $imageUrl = SectionAssets::resolve((string) ($content['image_url'] ?? ''), '');
        $mockUrl = htmlspecialchars(trim((string) ($content['mockup_url'] ?? 'https://example.com')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $alt = htmlspecialchars(trim((string) ($content['image_alt'] ?? 'Aperçu')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        if ($imageUrl !== '') {
            $data['hero_browser_html'] = '<div class="section-hero__browser">'
                . '<div class="section-hero__browser-chrome">'
                . '<span class="section-hero__browser-dot section-hero__browser-dot--red" aria-hidden="true"></span>'
                . '<span class="section-hero__browser-dot section-hero__browser-dot--yellow" aria-hidden="true"></span>'
                . '<span class="section-hero__browser-dot section-hero__browser-dot--green" aria-hidden="true"></span>'
                . '<span class="section-hero__browser-url">' . $mockUrl . '</span>'
                . '</div>'
                . '<img class="section-hero__browser-img" src="' . htmlspecialchars($imageUrl, ENT_QUOTES) . '" alt="' . $alt . '" width="1280" height="720" loading="lazy" decoding="async" />'
                . '</div>';
        }
        $data['hero_visual_html'] = '';

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichHero13(array $data, array $content, string $sectionId): array
    {
        $before = htmlspecialchars(trim((string) ($content['title'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $after = htmlspecialchars(trim((string) ($content['subheading'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $words = $content['flip_words'] ?? ['Composants', 'Blocs', 'Modèles'];
        if (!is_array($words)) {
            $words = ['Composants', 'Blocs', 'Modèles'];
        }
        $wordsHtml = '';
        foreach ($words as $word) {
            if (!is_scalar($word)) {
                continue;
            }
            $wordsHtml .= '<span class="section-hero__flip-word">' . htmlspecialchars((string) $word, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span>';
        }
        $data['hero_flip_html'] = '<h1 class="section-hero__flip-title">'
            . '<span class="section-hero__flip-before">' . $before . '</span> '
            . '<span class="section-hero__flip-rotator" data-hero-flip id="' . htmlspecialchars($sectionId, ENT_QUOTES) . '-flip">' . $wordsHtml . '</span> '
            . '<span class="section-hero__flip-after">' . $after . '</span>'
            . '</h1>';

        $items = is_array($content['items'] ?? null) ? array_slice($content['items'], 0, 3) : [];
        $features = '';
        $index = 0;
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $icon = self::FEATURE_ICONS[$index % count(self::FEATURE_ICONS)];
            $title = htmlspecialchars(trim((string) ($item['title'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $features .= '<li class="section-hero__flip-feature section-hero__flip-feature--' . $index . '">'
                . '<div class="section-hero__flip-feature-icon"><i class="' . $icon . '" aria-hidden="true"></i></div>'
                . '<p class="section-hero__flip-feature-label">' . $title . '</p>'
                . '</li>';
            $index++;
        }
        $data['hero_features_html'] = '<ul class="section-hero__flip-features">' . $features . '</ul>';

        return $data;
    }

    private static function dualImage(string $light, string $dark, string $alt, string $class): string
    {
        if ($light === '') {
            return '';
        }
        $html = '<img class="' . $class . ' ' . $class . '--light" src="' . htmlspecialchars($light, ENT_QUOTES) . '" alt="' . $alt . '" width="1280" height="720" loading="lazy" decoding="async" />';
        if ($dark !== '') {
            $html .= '<img class="' . $class . ' ' . $class . '--dark" src="' . htmlspecialchars($dark, ENT_QUOTES) . '" alt="' . $alt . '" width="1280" height="720" loading="lazy" decoding="async" />';
        }

        return $html;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function renderPhoneButtons(array $content): string
    {
        $buttons = is_array($content['buttons'] ?? null) ? $content['buttons'] : [];
        $primary = null;
        $secondary = null;
        foreach ($buttons as $button) {
            if (!is_array($button)) {
                continue;
            }
            $style = ($button['style'] ?? 'primary') === 'secondary' ? 'secondary' : 'primary';
            if ($style === 'primary' && $primary === null) {
                $primary = $button;
            } elseif ($style === 'secondary' && $secondary === null) {
                $secondary = $button;
            }
        }

        $parts = [];
        if (is_array($primary)) {
            $label = htmlspecialchars(trim((string) ($primary['label'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $href = htmlspecialchars(trim((string) ($primary['href'] ?? '#')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            if ($label !== '') {
                $parts[] = '<a class="section-hero__button--phone" href="' . $href . '">'
                    . '<i class="fa-solid fa-arrow-up-right" aria-hidden="true"></i>'
                    . '<span>' . $label . '</span></a>';
            }
        }
        if (is_array($secondary)) {
            $label = htmlspecialchars(trim((string) ($secondary['label'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $href = htmlspecialchars(trim((string) ($secondary['href'] ?? '#')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            if ($label !== '') {
                $parts[] = '<a class="section-hero__button--link" href="' . $href . '">' . $label . '</a>';
            }
        }

        return implode("\n", $parts);
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function renderBannerButtons(array $content): string
    {
        $buttons = is_array($content['buttons'] ?? null) ? $content['buttons'] : [];
        foreach ($buttons as $button) {
            if (!is_array($button)) {
                continue;
            }
            $label = trim((string) ($button['label'] ?? ''));
            $href = trim((string) ($button['href'] ?? ''));
            if ($label === '' || $href === '') {
                continue;
            }
            $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeHref = htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            return '<a class="section-hero__button section-hero__button--primary section-hero__button--calendar" href="' . $safeHref . '">'
                . '<i class="fa-solid fa-calendar" aria-hidden="true"></i> ' . $safeLabel . '</a>';
        }

        return '';
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function renderShowcaseButtons(array $content): string
    {
        $buttons = is_array($content['buttons'] ?? null) ? $content['buttons'] : [];
        foreach ($buttons as $button) {
            if (!is_array($button)) {
                continue;
            }
            if (($button['style'] ?? 'primary') === 'secondary') {
                continue;
            }
            $label = trim((string) ($button['label'] ?? ''));
            $href = trim((string) ($button['href'] ?? ''));
            if ($label === '' || $href === '') {
                continue;
            }

            return '<a class="section-hero__button section-hero__button--primary section-hero__button--lg section-hero__button--showcase" href="'
                . htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">'
                . htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                . ' <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>';
        }

        return '';
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function renderShowcaseVisual(array $content): string
    {
        $light = SectionAssets::resolve((string) ($content['image_url'] ?? ''), '');
        $dark = SectionAssets::resolve((string) ($content['image_url_dark'] ?? ''), '');
        if ($light === '') {
            return '';
        }

        $alt = htmlspecialchars(trim((string) ($content['image_alt'] ?? $content['title'] ?? 'Aperçu')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<div class="section-hero__showcase">'
            . self::dualImage($light, $dark, $alt, 'section-hero__showcase-img')
            . '</div>';
    }
}
