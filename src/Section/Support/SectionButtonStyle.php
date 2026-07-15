<?php

declare(strict_types=1);

namespace Capsule\Section\Support;

final class SectionButtonStyle
{
    /** @var list<string> */
    public const STYLES = ['primary', 'secondary', 'outline'];

    /** @var array<string, string> */
    public const LABELS = [
        'primary' => 'Principal',
        'secondary' => 'Secondaire',
        'outline' => 'Contour',
    ];

    public static function normalize(string $raw): string
    {
        $style = strtolower(trim($raw));

        return in_array($style, self::STYLES, true) ? $style : 'primary';
    }

    public static function sectionClass(string $raw): string
    {
        return 'section-button--' . self::normalize($raw);
    }

    public static function heroClass(string $raw): string
    {
        return 'section-hero__button--' . self::normalize($raw);
    }

    /**
     * @return array<string, string>
     */
    public static function selectOptions(): array
    {
        return self::LABELS;
    }
}
