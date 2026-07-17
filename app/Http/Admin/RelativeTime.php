<?php

declare(strict_types=1);

namespace App\Http\Admin;

final class RelativeTime
{
    public static function format(string $value, ?int $now = null): string
    {
        if ($value === '') {
            return '-';
        }

        $ts = strtotime($value . ' UTC');
        if ($ts === false) {
            return $value;
        }

        $now ??= time();
        $diff = $now - $ts;

        if ($diff < 60) {
            return 'à l\'instant';
        }
        if ($diff < 3600) {
            $mins = (int) floor($diff / 60);

            return 'il y a ' . $mins . ' minute' . ($mins > 1 ? 's' : '');
        }
        if ($diff < 86400) {
            $hours = (int) floor($diff / 3600);

            return 'il y a ' . $hours . ' heure' . ($hours > 1 ? 's' : '');
        }
        if ($diff < 604800) {
            $days = (int) floor($diff / 86400);

            return 'il y a ' . $days . ' jour' . ($days > 1 ? 's' : '');
        }
        if ($diff < 2592000) {
            $weeks = (int) floor($diff / 604800);

            return 'il y a ' . $weeks . ' semaine' . ($weeks > 1 ? 's' : '');
        }
        if ($diff < 31536000) {
            $months = (int) floor($diff / 2592000);

            return 'il y a ' . $months . ' mois';
        }

        $years = (int) floor($diff / 31536000);

        return 'il y a ' . $years . ' an' . ($years > 1 ? 's' : '');
    }

    public static function absolute(string $value): string
    {
        if ($value === '') {
            return '-';
        }

        $ts = strtotime($value . ' UTC');
        if ($ts === false) {
            return $value;
        }

        return date('d/m/Y à H:i', $ts);
    }
}
