<?php

declare(strict_types=1);

namespace App\Http\Dev\Defaults;

use Capsule\SectionAssets;

trait IntegrationsDefaults
{
    private static function integrationsContent(string $variant): array
    {
        $base = [
            'title' => 'Intégrations',
            'subtitle' => 'Connectez vos applications préférées à votre flux de travail.',
            'items' => self::integrationItems(),
        ];

        return match ($variant) {
            'integration9' => array_merge($base, [
                'title' => 'Intégrations disponibles',
                'subtitle' => '',
                'items' => self::integrationItems(extended: true),
            ]),
            default => $base,
        };
    }

    /**
     * @return list<array<string, string>>
     */
    private static function integrationItems(bool $extended = false): array
    {
        $items = [
            [
                'title' => 'Google Sheets',
                'text' => 'Synchronisez vos données avec Google Sheets pour automatiser vos flux.',
                'url' => SectionAssets::shared(self::INTEGRATIONS_SHARED, 'logos/google-icon.svg'),
            ],
            [
                'title' => 'Slack',
                'text' => 'Recevez mises à jour et notifications directement dans vos canaux Slack.',
                'url' => SectionAssets::shared(self::INTEGRATIONS_SHARED, 'logos/slack-icon.svg'),
            ],
            [
                'title' => 'Sketch',
                'text' => 'Importez vos designs Sketch et fluidifiez votre processus de conception.',
                'url' => SectionAssets::shared(self::INTEGRATIONS_SHARED, 'logos/sketch-icon.svg'),
            ],
            [
                'title' => 'Gatsby',
                'text' => 'Créez des sites ultra rapides grâce à l\'intégration Gatsby.',
                'url' => SectionAssets::shared(self::INTEGRATIONS_SHARED, 'logos/gatsby-icon.svg'),
            ],
            [
                'title' => 'Shopify',
                'text' => 'Synchronisez votre boutique Shopify et simplifiez la gestion des commandes.',
                'url' => SectionAssets::shared(self::INTEGRATIONS_SHARED, 'logos/spotify-icon.svg'),
            ],
            [
                'title' => 'Github',
                'text' => 'Automatisez vos workflows et suivez les changements avec l\'intégration Github.',
                'url' => SectionAssets::shared(self::INTEGRATIONS_SHARED, 'logos/github-icon.svg'),
            ],
        ];

        if ($extended) {
            $items[] = [
                'title' => 'Figma',
                'text' => 'Synchronisez vos maquettes Figma et adaptez votre processus de design.',
                'url' => SectionAssets::shared(self::INTEGRATIONS_SHARED, 'logos/figma-icon.svg'),
            ];
            $items[] = [
                'title' => 'Dropbox',
                'text' => 'Synchronisez vos fichiers Dropbox et simplifiez la gestion documentaire.',
                'url' => SectionAssets::shared(self::INTEGRATIONS_SHARED, 'logos/dropbox-icon.svg'),
            ];
        }

        return $items;
    }

    /**
     * @return array<string, mixed>
     */}
