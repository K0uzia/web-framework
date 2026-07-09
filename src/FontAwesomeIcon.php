<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Catalogue et normalisation des icônes Font Awesome (solid, hébergées en local).
 */
final class FontAwesomeIcon
{
  /** @var list<string> */
    public const FEATURE_DEFAULTS = [
        'fa-bolt',
        'fa-shield-halved',
        'fa-rocket',
        'fa-chart-line',
        'fa-wand-magic-sparkles',
        'fa-layer-group',
    ];

    /**
     * Icônes proposées dans l'éditeur de blocs.
     *
     * @return array<string, string> Clé glyphe (fa-bolt) => libellé
     */
    public static function catalog(): array
    {
        return [
            'fa-bolt' => 'Éclair',
            'fa-shield-halved' => 'Bouclier',
            'fa-rocket' => 'Fusée',
            'fa-chart-line' => 'Graphique',
            'fa-wand-magic-sparkles' => 'Magie',
            'fa-layer-group' => 'Calques',
            'fa-star' => 'Étoile',
            'fa-circle-check' => 'Validation',
            'fa-gear' => 'Réglages',
            'fa-lock' => 'Sécurité',
            'fa-globe' => 'Monde',
            'fa-cloud' => 'Cloud',
            'fa-database' => 'Données',
            'fa-code' => 'Code',
            'fa-mobile-screen' => 'Mobile',
            'fa-laptop' => 'Ordinateur',
            'fa-users' => 'Équipe',
            'fa-heart' => 'Favori',
            'fa-clock' => 'Rapidité',
            'fa-chart-pie' => 'Analyse',
            'fa-puzzle-piece' => 'Module',
            'fa-plug' => 'Intégration',
            'fa-image' => 'Image',
            'fa-video' => 'Vidéo',
            'fa-envelope' => 'Message',
            'fa-bell' => 'Alerte',
            'fa-lightbulb' => 'Idée',
            'fa-handshake' => 'Partenariat',
            'fa-gauge-high' => 'Performance',
            'fa-palette' => 'Design',
            'fa-briefcase' => 'Business',
            'fa-leaf' => 'Écologie',
        ];
    }

    public static function glyph(string $value, string $default = 'fa-circle'): string
    {
        $value = trim($value);
        if ($value === '') {
            return self::isValidGlyph($default) ? $default : 'fa-circle';
        }

        $value = preg_replace('/\s+/', ' ', $value) ?? $value;
        if (str_contains($value, ' ')) {
            $parts = explode(' ', $value);
            $value = end($parts) ?: $value;
        }

        if (!str_starts_with($value, 'fa-')) {
            $value = 'fa-' . ltrim($value, '-');
        }

        return self::isValidGlyph($value) ? $value : (self::isValidGlyph($default) ? $default : 'fa-circle');
    }

    public static function solidClass(string $value, string $default = 'fa-circle'): string
    {
        return 'fa-solid ' . self::glyph($value, $default);
    }

    public static function defaultForIndex(int $index): string
    {
        $icons = self::FEATURE_DEFAULTS;

        return $icons[max(0, $index - 1) % count($icons)];
    }

    private static function isValidGlyph(string $glyph): bool
    {
        return array_key_exists($glyph, self::catalog())
            || in_array($glyph, self::FEATURE_DEFAULTS, true);
    }
}
