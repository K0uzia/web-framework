<?php

declare(strict_types=1);

namespace App\Http\Dev\Defaults;

use Capsule\SectionAssets;

trait ComplianceDefaults
{
    private static function complianceContent(string $variant): array
    {
        return match ($variant) {
            default => [
                'tagline' => 'Conformité',
                'title' => 'Conformité et sécurité complètes',
                'text' => 'Restez conforme aux réglementations sur la vie privée et la santé. Notre plateforme répond aux exigences RGPD et HIPAA, avec protection des données et suivi de conformité pour les secteurs réglementés.',
                'logos' => self::complianceLogos(),
                'items' => self::complianceFeatures(),
            ],
        };
    }

    /**
     * @return list<array<string, string>>
     */
    private static function complianceLogos(): array
    {
        return [
            [
                'url' => SectionAssets::shared('compliance', 'GDPR.svg'),
                'label' => 'RGPD',
            ],
            [
                'url' => SectionAssets::shared('compliance', 'CCPA.svg'),
                'label' => 'CCPA',
            ],
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private static function complianceFeatures(): array
    {
        return [
            [
                'title' => 'Pistes d\'audit automatisées',
                'text' => 'Chaque action est journalisée et horodatée avec des pistes immuables pour une conformité réglementaire complète.',
                'url' => SectionAssets::shared('compliance', 'ISO-27001.svg'),
                'label' => 'ISO 27001',
            ],
            [
                'title' => 'Suivi de conformité',
                'text' => 'Une surveillance en temps réel garantit le respect continu des normes et réglementations du secteur.',
                'url' => SectionAssets::shared('compliance', 'ISO-27017.svg'),
                'label' => 'ISO 27017',
            ],
            [
                'title' => 'Reporting réglementaire',
                'text' => 'Générez automatiquement des rapports de conformité pour répondre aux exigences des audits.',
                'url' => SectionAssets::shared('compliance', 'ISO-27018.svg'),
                'label' => 'ISO 27018',
            ],
        ];
    }
}
