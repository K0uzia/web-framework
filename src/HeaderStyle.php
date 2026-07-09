<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Gabarits d'en-tête shadcnblocks.
 */
final class HeaderStyle
{
    public const TEMPLATE_DEFAULT = 'default';

    /** @var list<string> */
    public const BLOCK_TEMPLATES = [
        'navbar1',
        'navbar5',
    ];

    /** @var list<string> */
    public const ALL_TEMPLATES = [
        self::TEMPLATE_DEFAULT,
        'navbar1',
        'navbar5',
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
