<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Options d'affichage image et vidéo pour les blocs.
 */
final class MediaDisplaySettings
{
    /** @var list<string> */
    public const IMAGE_FITS = ['cover', 'contain', 'fill', 'none', 'scale-down'];

    /**
     * @param array<string, mixed> $content
     */
    public static function imageFit(array $content, string $default = 'cover'): string
    {
        $fit = trim((string) ($content['image_fit'] ?? ''));

        return in_array($fit, self::IMAGE_FITS, true) ? $fit : $default;
    }

    /**
     * @param array<string, mixed> $content
     */
    public static function videoFit(array $content, string $default = 'cover'): string
    {
        $fit = trim((string) ($content['video_fit'] ?? ''));
        $allowed = ['cover', 'contain', 'fill'];

        if (in_array($fit, $allowed, true)) {
            return $fit;
        }
        if (in_array($fit, ['none', 'scale-down'], true)) {
            return 'contain';
        }

        return $default;
    }

    /**
     * @param array<string, mixed> $content
     */
    public static function imageFitClass(array $content, string $baseClass, string $default = 'cover'): string
    {
        return $baseClass . '--fit-' . self::imageFit($content, $default);
    }

    /**
     * @param array<string, mixed> $content
     */
    public static function videoFitClass(array $content, string $baseClass, string $default = 'cover'): string
    {
        return $baseClass . '--fit-' . self::videoFit($content, $default);
    }

    /**
     * @param array<string, mixed> $content
     *
     * @return array{autoplay: bool, muted: bool, loop: bool, controls: bool}
     */
    public static function videoFlags(array $content, string $context = 'embed'): array
    {
        $defaults = $context === 'background'
            ? ['autoplay' => true, 'muted' => true, 'loop' => true, 'controls' => false]
            : ['autoplay' => false, 'muted' => true, 'loop' => false, 'controls' => true];

        return [
            'autoplay' => self::flag($content, 'video_autoplay', $defaults['autoplay']),
            'muted' => self::flag($content, 'video_muted', $defaults['muted']),
            'loop' => self::flag($content, 'video_loop', $defaults['loop']),
            'controls' => self::flag($content, 'video_controls', $defaults['controls']),
        ];
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function flag(array $content, string $key, bool $default): bool
    {
        $value = trim((string) ($content[$key] ?? ''));
        if ($value === '') {
            return $default;
        }

        return in_array($value, ['1', 'on', 'yes', 'true'], true);
    }
}
