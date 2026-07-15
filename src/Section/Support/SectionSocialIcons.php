<?php

declare(strict_types=1);

namespace Capsule\Section\Support;

final class SectionSocialIcons
{
    /** @var array<string, string> */
    private const ICON_MAP = [
        'instagram' => 'fa-brands fa-instagram',
        'facebook' => 'fa-brands fa-facebook-f',
        'linkedin' => 'fa-brands fa-linkedin',
        'github' => 'fa-brands fa-github',
        'discord' => 'fa-brands fa-discord',
        'x' => 'fa-brands fa-x-twitter',
        'twitter' => 'fa-brands fa-x-twitter',
        'youtube' => 'fa-brands fa-youtube',
    ];

    public static function iconClass(string $network): string
    {
        $key = strtolower(trim($network));

        return self::ICON_MAP[$key] ?? 'fa-solid fa-link';
    }
}
