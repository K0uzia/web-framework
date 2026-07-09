<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Gabarits de pied de page shadcnblocks.
 */
final class FooterStyle
{
    public const TEMPLATE_DEFAULT = 'default';

    /** @var list<string> */
    public const BLOCK_TEMPLATES = [
        'footer2',
        'footer7',
    ];

    /** @var list<string> */
    public const ALL_TEMPLATES = [
        self::TEMPLATE_DEFAULT,
        'footer2',
        'footer7',
    ];

    public static function isBlocksTemplate(string $template): bool
    {
        return in_array($template, self::BLOCK_TEMPLATES, true);
    }

    public static function normalizeTemplate(string $template): string
    {
        return in_array($template, self::ALL_TEMPLATES, true) ? $template : self::TEMPLATE_DEFAULT;
    }
}
