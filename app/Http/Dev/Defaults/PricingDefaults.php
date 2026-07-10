<?php

declare(strict_types=1);

namespace App\Http\Dev\Defaults;

use Capsule\SectionAssets;

trait PricingDefaults
{
    private static function pricingContent(string $variant): array
    {
        return match ($variant) {
            'pricing4' => [
                'title' => 'Offres simples et transparentes',
                'subtitle' => 'Choisissez l\'offre adaptée à vos besoins. Commencez gratuitement et évoluez à votre rythme.',
                'billing_monthly_label' => 'Mensuel',
                'billing_yearly_label' => 'Annuel',
                'items' => self::pricing4Plans(),
            ],
            'pricing6' => [
                'title' => 'Nos tarifs',
                'subtitle' => 'Une offre unique avec les outils nécessaires pour livrer plus vite.',
                'price_monthly' => '49',
                'period_monthly' => '/mois',
                'button_label' => 'Commencer',
                'button_href' => '#',
                'items' => self::pricing6FeatureGroups(),
            ],
            'pricing11' => [
                'title' => 'Offres tarifaires',
                'subtitle' => 'Comparez les formules et choisissez celle qui correspond à votre équipe.',
                'billing_monthly_label' => 'Mensuel',
                'billing_yearly_label' => 'Annuel',
                'items' => self::pricing11Plans(),
            ],
            default => [
                'title' => 'Tarifs',
                'subtitle' => 'Découvrez nos offres accessibles et adaptées à chaque besoin.',
                'billing_monthly_label' => 'Mensuel',
                'billing_yearly_label' => 'Annuel',
                'items' => self::pricing2Plans(),
            ],
        };
    }

    /**
     * @return list<array<string, string>>
     */
    private static function pricing2Plans(): array
    {
        return [
            [
                'title' => 'Gratuit',
                'text' => 'Pour les particuliers qui débutent',
                'price_monthly' => '$0',
                'price_yearly' => '$0',
                'features' => "Utilisateur unique\nBibliothèque de composants de base\nSupport communautaire\n1 Go d'espace de stockage",
                'label' => 'Commencer',
                'href' => '#',
            ],
            [
                'title' => 'Pro',
                'text' => 'Pour les professionnels',
                'price_monthly' => '$49',
                'price_yearly' => '$359',
                'features' => "Jusqu'à 5 membres\nBibliothèque avancée\nSupport prioritaire\n2 Go d'espace de stockage\nCollaboration d'équipe\nPersonnalisation de marque",
                'label' => 'Acheter',
                'href' => '#',
                'highlighted' => '1',
            ],
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private static function pricing4Plans(): array
    {
        return [
            [
                'title' => 'Offre Basique',
                'price_monthly' => '$0',
                'price_yearly' => '$0',
                'period_monthly' => 'Par mois',
                'period_yearly' => 'Par an',
                'features' => "Jusqu'à 5 composants\nSupport communautaire\nMises à jour hebdomadaires\n100 Mo de stockage\nAnalytiques de base",
                'label' => 'Commencer gratuitement',
                'href' => '#',
            ],
            [
                'title' => 'Offre Standard',
                'price_monthly' => '$20',
                'price_yearly' => '$200',
                'period_monthly' => 'Par mois',
                'period_yearly' => 'Par an',
                'features' => "Composants illimités\nSupport prioritaire\nMises à jour quotidiennes\n10 Go de stockage\nAnalytiques avancées",
                'label' => 'Choisir cette offre',
                'href' => '#',
                'highlighted' => '1',
            ],
            [
                'title' => 'Offre Premium',
                'price_monthly' => '$80',
                'price_yearly' => '$800',
                'period_monthly' => 'Par mois',
                'period_yearly' => 'Par an',
                'features' => "Composants illimités\nSupport dédié\nMises à jour en temps réel\nStockage illimité\nIntégrations personnalisées",
                'label' => 'Acheter',
                'href' => '#',
            ],
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private static function pricing6FeatureGroups(): array
    {
        return [
            ['features' => "Illimité\nIntégrations\nSupport 24/7"],
            ['features' => "Collaboration en direct\nStockage illimité\nSatisfait ou remboursé 30 jours"],
            ['features' => "Membres illimités\nPersonnalisation\nUtilisateurs illimités"],
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private static function pricing11Plans(): array
    {
        return [
            [
                'title' => 'Gratuit',
                'price_monthly' => '$9',
                'price_yearly' => '$9',
                'href' => '#',
            ],
            [
                'title' => 'Basique',
                'price_monthly' => '$50',
                'price_yearly' => '$45',
                'href' => '#',
            ],
            [
                'title' => 'Équipe',
                'price_monthly' => '$100',
                'price_yearly' => '$90',
                'href' => '#',
                'highlighted' => '1',
            ],
            [
                'title' => 'Entreprise',
                'price_monthly' => '$200',
                'price_yearly' => '$160',
                'href' => '#',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */}
