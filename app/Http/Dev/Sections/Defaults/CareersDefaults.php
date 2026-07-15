<?php

declare(strict_types=1);

namespace App\Http\Dev\Sections\Defaults;

trait CareersDefaults
{
    private static function careersContent(string $variant): array
    {
        return match ($variant) {
            'careers4' => [
                'title' => 'Carrières',
                'subtitle' => 'Chaque catégorie liste les postes ouverts avec le lieu précis, pour que les candidats sachent si des déplacements sont nécessaires.',
                'text' => 'Cliquez sur un intitulé pour ouvrir la fiche complète sur votre ATS. La flèche à droite mène au même lien.',
                'items' => self::careers4Items(),
            ],
            default => [
                'title' => 'Carrières',
                'subtitle' => "Nous publions les postes dès qu'un manager ouvre un recrutement. Les équipes sont regroupées pour faciliter la recherche par fonction.\n\nChaque ligne mène à la fiche complète sur votre ATS ou site carrières. Les lieux remote et bureaux apparaissent en sous-titre.\n\nSi aucun poste ne correspond aujourd'hui, les candidatures spontanées restent les bienvenues via le formulaire de contact.",
                'items' => self::careers1Items(),
            ],
        };
    }

    /**
     * @return list<array<string, string>>
     */
    private static function careers1Items(): array
    {
        return [
            ['group' => 'Ventes', 'title' => 'Responsable commercial', 'label' => 'Londres', 'href' => '#'],
            ['group' => 'Ventes', 'title' => 'Chargé de développement commercial', 'label' => 'Londres', 'href' => '#'],
            ['group' => 'Ventes', 'title' => 'Responsable grands comptes', 'label' => 'Londres', 'href' => '#'],
            ['group' => 'Customer Success', 'title' => 'Chargé de relation client', 'label' => 'Londres', 'href' => '#'],
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private static function careers4Items(): array
    {
        return [
            ['group' => 'Ingénierie', 'title' => 'Développeur frontend senior', 'label' => 'Remote', 'href' => '#'],
            ['group' => 'Ingénierie', 'title' => 'Designer UI/UX', 'label' => 'San Francisco', 'href' => '#'],
            ['group' => 'Ingénierie', 'title' => 'Développeur React', 'label' => 'Remote', 'href' => '#'],
            ['group' => 'Ingénierie', 'title' => 'Lead technique', 'label' => 'Londres', 'href' => '#'],
            ['group' => 'Design', 'title' => 'Product designer', 'label' => 'Remote', 'href' => '#'],
            ['group' => 'Design', 'title' => 'Designer visuel', 'label' => 'Berlin', 'href' => '#'],
        ];
    }
}
