<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Type de fond média pour les blocs plein écran (hero78).
 */
final class BackgroundType
{
    public const IMAGE = 'image';
    public const VIDEO = 'video';
    public const SHADER = 'shader';

    public static function normalize(string $value): string
    {
        $value = trim(strtolower($value));

        return match ($value) {
            self::VIDEO, self::SHADER => $value,
            default => self::IMAGE,
        };
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::IMAGE => 'Image',
            self::VIDEO => 'Vidéo',
            self::SHADER => 'Shader animé',
        ];
    }
}
