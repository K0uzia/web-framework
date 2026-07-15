<?php

declare(strict_types=1);

namespace App\Http\Dev\Sections\Defaults;

use Capsule\SectionAssets;

trait ChangelogDefaults
{
    /**
     * @return array<string, mixed>
     */
    private static function changelogContent(string $variant): array
    {
        return match ($variant) {
            default => [
                'title' => 'Changelog',
                'subtitle' => 'Retrouvez les dernières mises à jour et améliorations de la plateforme.',
                'items' => self::changelog1Items(),
            ],
        };
    }

    /**
     * @return list<array<string, string>>
     */
    private static function changelog1Items(): array
    {
        $image = SectionAssets::shared(self::FEATURES_SHARED, 'placeholder-1.svg');

        return [
            [
                'label' => 'Version 1.3.0',
                'date' => '15 novembre 2024',
                'title' => 'Tableau de bord analytique amélioré',
                'text' => 'Nous avons repensé le tableau de bord analytique pour offrir des indicateurs plus profonds et des visualisations plus claires.',
                'features' => "Visualisations interactives avec mises à jour en temps réel\nWidgets de tableau de bord personnalisables\nExport des analyses en CSV, PDF et Excel\nNouveaux modèles de rapports\nFiltres et segments de données affinés",
                'url' => $image,
                'href' => 'https://example.com',
                'cta_label' => 'En savoir plus',
            ],
            [
                'label' => 'Version 1.2.5',
                'date' => '7 octobre 2024',
                'title' => 'Lancement de l\'application mobile',
                'text' => 'Notre application mobile est disponible sur iOS et Android.',
                'features' => "Expérience mobile native pour travailler en déplacement\nMode hors ligne\nNotifications push pour les mises à jour importantes\nAuthentification biométrique",
            ],
            [
                'label' => 'Version 1.2.1',
                'date' => '23 septembre 2024',
                'title' => 'Nouvelles fonctionnalités et améliorations',
                'text' => 'Voici les dernières évolutions de la plateforme. Nous améliorons en continu votre expérience.',
                'features' => "Export des données\nPerformances accrues\nCorrections de bugs mineurs\nImport des données",
                'url' => $image,
            ],
            [
                'label' => 'Version 1.0.0',
                'date' => '31 août 2024',
                'title' => 'Première version de la plateforme',
                'text' => 'Découvrez une nouvelle plateforme pour gérer vos projets et vos tâches. Nous sommes ravis de vous accompagner dès le départ.',
                'url' => $image,
                'href' => 'https://example.com',
                'cta_label' => 'En savoir plus',
            ],
        ];
    }
}
