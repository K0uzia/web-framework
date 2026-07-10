<?php

declare(strict_types=1);

namespace App\Http\Dev\Defaults;

use Capsule\SectionAssets;

trait DownloadDefaults
{
    private static function downloadContent(string $variant): array
    {
        return match ($variant) {
            'download2' => [
                'title' => 'Téléchargement',
                'subtitle' => 'Choisissez votre plateforme et commencez à utiliser notre application tout de suite. Disponible sur les principaux appareils et systèmes.',
                'desktop_title' => 'PC / Mac',
                'desktop_text' => 'Solution complète pour ordinateur.',
                'desktop_button_label' => 'Télécharger',
                'desktop_href' => '#',
                'ios_title' => 'iOS',
                'ios_text' => 'Conçue spécifiquement pour les appareils iOS.',
                'ios_href' => '#',
                'android_title' => 'Android',
                'android_text' => 'Optimisée pour l\'écosystème Android.',
                'android_href' => '#',
            ],
            default => [
                'title' => 'Téléchargez notre application',
                'subtitle' => 'Installez notre application sur chaque plateforme et accélérez votre travail grâce aux fonctionnalités synchronisées et à la collaboration en temps réel.',
                'desktop_heading' => 'Ordinateur',
                'desktop_title' => 'PC / Mac',
                'desktop_text' => 'Profitez de toutes les fonctionnalités avec notre version complète pour ordinateur.',
                'desktop_button_label' => 'Télécharger',
                'desktop_href' => '#',
                'ios_heading' => 'Téléphone',
                'ios_title' => 'iOS',
                'ios_text' => 'Emportez votre productivité partout avec une application mobile pensée pour le tactile.',
                'ios_href' => '#',
                'android_heading' => 'Téléphone / tablette',
                'android_title' => 'Android',
                'android_text' => 'Une expérience Android optimisée sur téléphone et tablette, flexible et fluide.',
                'android_href' => '#',
            ],
        };
    }

    /**
     * @return list<array<string, string>>
     */}
