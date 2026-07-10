<?php

declare(strict_types=1);

namespace App\Http\Dev\Defaults;

use Capsule\SectionAssets;

trait HeroDefaults
{
    private static function heroContent(string $variant): array
    {
        return match ($variant) {
            'hero1' => self::heroSplitBadgeDefaults('Changelog v1.1'),
            'hero3' => self::heroSplitReviewsDefaults(),
            'hero7' => self::heroSocialProofDefaults(),
            'hero12' => self::heroLogoStackDefaults(),
            'hero34' => self::heroSplitBadgeDefaults('Changelog v1.1'),
            'hero45' => self::heroFeatureSliderDefaults(),
            'hero47' => self::heroPhoneMockupDefaults(),
            'hero67' => self::heroLogoBannerDefaults(),
            'hero78' => self::heroFullscreenDefaults(),
            'hero115' => self::heroDecorativeDefaults(),
            'hero195' => self::heroTabsDefaults(),
            'hero206' => self::heroMarqueeDefaults(),
            'hero243' => self::heroFlipDefaults(),
            default => self::heroSplitReviewsDefaults(),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private static function heroSplitBadgeDefaults(string $badge): array
    {
        return [
            'badge' => $badge,
            'title' => 'Des blocs prêts à l\'emploi',
            'subtitle' => 'Composants soignés pour vos pages marketing. Copiez, adaptez et publiez en quelques minutes.',
            'image_url' => SectionAssets::shared(self::SHARED, 'saas-hero-1-16x9.png'),
            'image_url_dark' => SectionAssets::shared(self::SHARED, 'saas-hero-1-16x9-dark.png'),
            'image_alt' => 'Aperçu produit',
            'buttons' => self::primarySecondaryButtons('Parcourir', 'Voir le code'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function heroSplitReviewsDefaults(): array
    {
        return [
            'title' => 'Des blocs prêts à l\'emploi',
            'subtitle' => 'Composants soignés pour vos pages marketing. Copiez, adaptez et publiez en quelques minutes.',
            'image_url' => SectionAssets::shared(self::SHARED, 'saas-hero-1-16x9.png'),
            'image_url_dark' => SectionAssets::shared(self::SHARED, 'saas-hero-1-16x9-dark.png'),
            'image_alt' => 'Aperçu produit',
            'reviews_rating' => '5.0',
            'reviews_count' => '200',
            'review_avatars' => self::reviewAvatars(),
            'buttons' => self::primarySecondaryButtons('Parcourir', 'Voir le code'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function heroSocialProofDefaults(): array
    {
        return [
            'title' => 'Le logiciel marketing dont vos équipes ont besoin.',
            'subtitle' => 'Planifiez vos campagnes, suivez les résultats et accélérez votre croissance avec des outils fiables.',
            'reviews_rating' => '4.9',
            'reviews_count' => '206',
            'review_avatars' => self::reviewAvatars(),
            'buttons' => [
                ['label' => 'Commencer', 'href' => '#', 'style' => 'primary'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function heroLogoStackDefaults(): array
    {
        return [
            'title' => 'Construisez votre prochain projet avec des blocs',
            'subtitle' => 'Une base solide pour lancer rapidement des pages modernes, accessibles et performantes.',
            'buttons' => self::primarySecondaryButtons('Commencer', 'En savoir plus'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function heroFeatureSliderDefaults(): array
    {
        $slides = [
            ['saas-hero-2-16x9.png', 'saas-hero-2-16x9-dark.png'],
            ['saas-hero-3-16x9.png', 'saas-hero-3-16x9-dark.png'],
            ['saas-hero-4-16x9.png', 'saas-hero-4-16x9-dark.png'],
        ];
        $items = [];
        $titles = [
            ['Patterns composables', 'Structurez vos sections avec des espacements cohérents.'],
            ['Design tokens', 'Couleurs, typographie et rayons depuis une seule source.'],
            ['Accessibilité', 'Bases clavier et lecteur d\'écran dès le départ.'],
        ];
        foreach ($titles as $i => [$title, $text]) {
            $items[] = [
                'title' => $title,
                'text' => $text,
                'url' => SectionAssets::shared(self::SHARED, $slides[$i][0]),
                'href' => SectionAssets::shared(self::SHARED, $slides[$i][1]),
            ];
        }

        return [
            'badge' => 'Plateforme',
            'title' => 'Des composants pensés pour la stack moderne.',
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function heroPhoneMockupDefaults(): array
    {
        return [
            'title' => 'Des blocs',
            'subheading' => ' pour vos pages produit',
            'subtitle' => 'Composants soignés pour React et Tailwind. Intégrez-les directement dans votre projet.',
            'image_url' => SectionAssets::shared(self::SHARED, 'placeholder-dark-7-tall.svg'),
            'image_alt' => 'Capture écran mobile',
            'buttons' => self::primarySecondaryButtons('Commencer', 'Lire la doc'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function heroLogoBannerDefaults(): array
    {
        return [
            'title' => 'Créez des expériences en ligne remarquables',
            'subtitle' => 'Un site qui capte l\'attention, engage vos visiteurs et atteint vos objectifs en quelques jours.',
            'image_url' => SectionAssets::shared(self::SHARED, 'placeholder-1.svg'),
            'buttons' => [
                ['label' => 'Commencer aujourd\'hui', 'href' => '#', 'style' => 'primary'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function heroFullscreenDefaults(): array
    {
        return [
            'title' => 'Explorez les merveilles de la science.',
            'subtitle' => 'Des gratte-ciel aux ponts innovants, chaque photo invite à découvrir les prouesses humaines.',
            'background_image_url' => SectionAssets::shared(self::SHARED, 'backgrounds/fullscreen-architecture.jpg'),
            'buttons' => [
                ['label' => 'Voir toutes les photos', 'href' => '#', 'style' => 'primary'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function heroDecorativeDefaults(): array
    {
        return [
            'title' => 'Des blocs prêts à l\'emploi',
            'subtitle' => 'Composants soignés pour vos pages marketing. Copiez, adaptez et publiez en quelques minutes.',
            'byline' => 'Adopté par plus de 25 000 entreprises',
            'image_url' => SectionAssets::shared(self::SHARED, 'saas-hero-1-16x9.png'),
            'image_url_dark' => SectionAssets::shared(self::SHARED, 'saas-hero-1-16x9-dark.png'),
            'image_alt' => 'Aperçu produit',
            'buttons' => self::primarySecondaryButtons('Parcourir', 'Voir le code'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function heroTabsDefaults(): array
    {
        $tabs = [];
        $labels = ['Insights', 'Métriques', 'Tendances', 'Sources', 'Modèles'];
        for ($i = 1; $i <= 5; $i++) {
            $tabs[] = [
                'title' => $labels[$i - 1],
                'url' => SectionAssets::shared(self::SHARED, 'saas-hero-' . $i . '-16x9.png'),
                'href' => SectionAssets::shared(self::SHARED, 'saas-hero-' . $i . '-16x9-dark.png'),
            ];
        }

        return [
            'title' => 'La solution CRM propulsée par l\'IA.',
            'subtitle' => 'Gérez comptes, deals et handoffs au même endroit. Automatisez vos workflows avec des insights en temps réel.',
            'items' => $tabs,
            'buttons' => self::primarySecondaryButtons('S\'inscrire', 'En savoir plus'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function heroMarqueeDefaults(): array
    {
        $logos = [];
        for ($i = 1; $i <= 12; $i++) {
            $logos[] = [
                'title' => 'Logo ' . $i,
                'url' => SectionAssets::shared(self::SHARED, 'logos/fictional-company-logo-' . $i . '.svg'),
            ];
        }

        return [
            'title' => 'La solution CRM propulsée par l\'IA.',
            'subtitle' => 'Gérez comptes, deals et handoffs au même endroit. Automatisez vos workflows avec des insights en temps réel.',
            'items' => $logos,
            'image_url' => SectionAssets::shared(self::SHARED, 'saas-hero-2-16x9.png'),
            'image_alt' => 'Aperçu interface produit',
            'mockup_url' => 'https://exemple.fr',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function heroFlipDefaults(): array
    {
        return [
            'badge' => 'Plateforme',
            'title' => 'Interface',
            'subheading' => 'pour la stack moderne.',
            'flip_words' => ['Composants', 'Blocs', 'Modèles'],
            'subtitle' => 'Une base performante, accessible et prête pour la production.',
            'items' => [
                ['title' => 'Patterns composables', 'text' => 'Sections structurées et espacements cohérents.'],
                ['title' => 'Design tokens', 'text' => 'Thème centralisé pour couleurs et typographie.'],
                ['title' => 'Accessibilité', 'text' => 'Bases clavier et lecteur d\'écran incluses.'],
            ],
            'buttons' => [
                ['label' => 'Parcourir les blocs', 'href' => '#', 'style' => 'primary'],
            ],
        ];
    }

    /**
     * @return list<array{url: string, title: string}>
     */}
