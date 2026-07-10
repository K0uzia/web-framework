<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Rendu HTML spécifique aux variantes download (conversion des blocs React).
 */
final class DownloadVariantRenderer
{
    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    public static function enrich(array $data, array $content, string $variant): array
    {
        $data['download_cards_html'] = match ($variant) {
            'download1' => self::cardsDownload1Html($content),
            'download2' => self::columnsDownload2Html($content),
            default => '',
        };

        return $data;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function cardsDownload1Html(array $content): string
    {
        $desktopButton = trim((string) ($content['desktop_button_label'] ?? ''));
        if ($desktopButton === '') {
            $desktopButton = 'Télécharger';
        }
        $safeDesktopButton = htmlspecialchars($desktopButton, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return self::desktopCardHtml($content, $safeDesktopButton)
            . self::mobileCardHtml($content, 'ios', 'fa-solid fa-mobile-screen')
            . self::mobileCardHtml($content, 'android', 'fa-solid fa-tablet-screen-button');
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function desktopCardHtml(array $content, string $buttonLabel): string
    {
        $heading = self::text($content, 'desktop_heading');
        $title = self::text($content, 'desktop_title');
        $text = self::text($content, 'desktop_text');
        $href = self::href((string) ($content['desktop_href'] ?? '#'));

        return '<article class="section-download__card section-download__card--desktop section-download__card--featured">'
            . '<div class="section-download__icon-wrap section-download__icon-wrap--featured" aria-hidden="true">'
            . '<i class="fa-solid fa-desktop"></i>'
            . '</div>'
            . '<div class="section-download__card-body">'
            . ($heading !== '' ? '<p class="section-download__card-heading">' . $heading . '</p>' : '')
            . ($title !== '' ? '<h3 class="section-download__card-title">' . $title . '</h3>' : '')
            . ($text !== '' ? '<p class="section-download__card-text">' . $text . '</p>' : '')
            . '</div>'
            . '<div class="section-download__card-action">'
            . '<a class="section-download__btn section-button section-button--primary" href="' . $href . '">'
            . '<i class="fa-solid fa-download" aria-hidden="true"></i>'
            . '<span>' . $buttonLabel . '</span></a>'
            . '</div>'
            . '</article>';
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function mobileCardHtml(
        array $content,
        string $platform,
        string $iconClass,
    ): string {
        $heading = self::text($content, $platform . '_heading');
        $title = self::text($content, $platform . '_title');
        $text = self::text($content, $platform . '_text');
        $href = self::href((string) ($content[$platform . '_href'] ?? '#'));

        return '<article class="section-download__card section-download__card--' . $platform . '">'
            . '<div class="section-download__icon-wrap" aria-hidden="true">'
            . '<i class="' . $iconClass . '"></i>'
            . '</div>'
            . '<div class="section-download__card-body">'
            . ($heading !== '' ? '<p class="section-download__card-heading section-download__card-heading--muted">' . $heading . '</p>' : '')
            . ($title !== '' ? '<h3 class="section-download__card-title">' . $title . '</h3>' : '')
            . ($text !== '' ? '<p class="section-download__card-text section-download__card-text--muted">' . $text . '</p>' : '')
            . '</div>'
            . '<div class="section-download__card-action">'
            . self::storeButtonHtml($platform, $href)
            . '</div>'
            . '</article>';
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function columnsDownload2Html(array $content): string
    {
        $desktopButton = trim((string) ($content['desktop_button_label'] ?? ''));
        if ($desktopButton === '') {
            $desktopButton = 'Télécharger';
        }
        $safeDesktopButton = htmlspecialchars($desktopButton, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return self::desktopColumnHtml($content, $safeDesktopButton)
            . self::mobileColumnHtml($content, 'ios', 'fa-solid fa-mobile-screen')
            . self::mobileColumnHtml($content, 'android', 'fa-solid fa-tablet-screen-button');
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function desktopColumnHtml(array $content, string $buttonLabel): string
    {
        $title = self::text($content, 'desktop_title');
        $text = self::text($content, 'desktop_text');
        $href = self::href((string) ($content['desktop_href'] ?? '#'));

        return '<div class="section-download__column section-download__column--desktop">'
            . '<div class="section-download__icon-wrap section-download__icon-wrap--large" aria-hidden="true">'
            . '<i class="fa-solid fa-desktop"></i>'
            . '</div>'
            . ($title !== '' ? '<h3 class="section-download__column-title">' . $title . '</h3>' : '')
            . ($text !== '' ? '<p class="section-download__column-text">' . $text . '</p>' : '')
            . '<a class="section-download__btn section-download__btn--centered section-button section-button--primary" href="' . $href . '">'
            . '<i class="fa-solid fa-download" aria-hidden="true"></i>'
            . '<span>' . $buttonLabel . '</span></a>'
            . '</div>';
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function mobileColumnHtml(
        array $content,
        string $platform,
        string $iconClass,
    ): string {
        $title = self::text($content, $platform . '_title');
        $text = self::text($content, $platform . '_text');
        $href = self::href((string) ($content[$platform . '_href'] ?? '#'));

        return '<div class="section-download__column section-download__column--' . $platform . '">'
            . '<div class="section-download__icon-wrap section-download__icon-wrap--large" aria-hidden="true">'
            . '<i class="' . $iconClass . '"></i>'
            . '</div>'
            . ($title !== '' ? '<h3 class="section-download__column-title">' . $title . '</h3>' : '')
            . ($text !== '' ? '<p class="section-download__column-text">' . $text . '</p>' : '')
            . self::storeButtonHtml($platform, $href, true)
            . '</div>';
    }

    private static function storeButtonHtml(string $platform, string $href, bool $centered = false): string
    {
        $isIos = $platform === 'ios';
        $iconClass = $isIos ? 'fa-brands fa-apple' : 'fa-brands fa-google-play';
        $kicker = $isIos ? 'Télécharger dans' : 'Disponible sur';
        $label = $isIos ? 'l\'App Store' : 'Google Play';
        $ariaLabel = $isIos ? 'Télécharger sur l\'App Store' : 'Disponible sur Google Play';
        $safeKicker = htmlspecialchars($kicker, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeAria = htmlspecialchars($ariaLabel . ' (ouvre un nouvel onglet)', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $centeredClass = $centered ? ' section-download__store-btn--centered' : '';

        return '<a class="section-download__store-btn section-download__store-btn--' . $platform . $centeredClass . '" href="' . $href . '"'
            . ' target="_blank" rel="noopener noreferrer" aria-label="' . $safeAria . '">'
            . '<span class="section-download__store-btn-icon" aria-hidden="true"><i class="' . $iconClass . '"></i></span>'
            . '<span class="section-download__store-btn-text">'
            . '<span class="section-download__store-btn-kicker">' . $safeKicker . '</span>'
            . '<span class="section-download__store-btn-label">' . $safeLabel . '</span>'
            . '</span></a>';
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function text(array $content, string $key): string
    {
        $value = trim((string) ($content[$key] ?? ''));
        if ($value === '') {
            return '';
        }

        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private static function href(string $href): string
    {
        $trimmed = trim($href);

        return htmlspecialchars($trimmed !== '' ? $trimmed : '#', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
