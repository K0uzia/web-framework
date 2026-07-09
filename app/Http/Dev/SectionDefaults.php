<?php

declare(strict_types=1);

namespace App\Http\Dev;

use Capsule\ContactStyle;
use Capsule\FeatureStyle;
use Capsule\HeroStyle;
use Capsule\IntegrationStyle;
use Capsule\PricingStyle;
use Capsule\SectionAssets;
use Capsule\TestimonialStyle;

/**
 * Contenus et styles par défaut des blocs section.
 */
final class SectionDefaults
{
    private const SHARED = 'hero';
    private const FEATURES_SHARED = 'features';
    private const INTEGRATIONS_SHARED = 'integrations';
    private const PRICING_SHARED = 'pricing';

    /**
     * @return array<string, mixed>
     */
    public static function content(string $type, string $variant = ''): array
    {
        if ($type === 'hero') {
            $variant = HeroStyle::normalizeVariant($variant !== '' ? $variant : 'hero3');

            return self::heroContent($variant);
        }
        if ($type === 'features') {
            $variant = FeatureStyle::normalizeVariant($variant !== '' ? $variant : 'feature3');

            return self::featuresContent($variant);
        }
        if ($type === 'integrations') {
            $variant = IntegrationStyle::normalizeVariant($variant !== '' ? $variant : 'integration3');

            return self::integrationsContent($variant);
        }
        if ($type === 'pricing') {
            $variant = PricingStyle::normalizeVariant($variant !== '' ? $variant : 'pricing2');

            return self::pricingContent($variant);
        }
        if ($type === 'contact') {
            $variant = ContactStyle::normalizeVariant($variant !== '' ? $variant : 'contact2');

            return self::contactContent($variant);
        }
        if ($type === 'testimonials') {
            $variant = TestimonialStyle::normalizeVariant($variant !== '' ? $variant : 'testimonial4');

            return self::testimonialsContent($variant);
        }

        return [];
    }

    /**
     * @return array<string, string>
     */
    public static function style(string $type): array
    {
        return match ($type) {
            'hero' => ['bg' => 'background', 'padding' => 'xl'],
            'features' => ['bg' => 'background', 'padding' => 'xl'],
            'integrations' => IntegrationStyle::defaults('integration3'),
            'pricing' => PricingStyle::defaults('pricing2'),
            'contact' => ContactStyle::defaults('contact2'),
            'testimonials' => TestimonialStyle::defaults('testimonial4'),
            default => ['padding' => 'md'],
        };
    }

    /**
     * @return array<string, mixed>
     */
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
     */
    private static function reviewAvatars(): array
    {
        $names = ['Mia Chen', 'Marcus Rivera', 'Priya Sharma', 'James Okafor', 'Sofia Chen'];
        $avatars = [];
        foreach ($names as $index => $name) {
            $avatars[] = [
                'url' => SectionAssets::shared(self::SHARED, 'avatars/avatar' . ($index + 1) . '.jpg'),
                'title' => $name,
            ];
        }

        return $avatars;
    }

    /**
     * @return list<array{label: string, href: string, style: string}>
     */
    private static function primarySecondaryButtons(string $primary, string $secondary): array
    {
        return [
            ['label' => $primary, 'href' => '#', 'style' => 'primary'],
            ['label' => $secondary, 'href' => '#', 'style' => 'secondary'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function featuresContent(string $variant): array
    {
        $base = [
            'title' => 'Construisez plus vite avec des fonctionnalités prêtes pour la production',
            'subtitle' => 'Chaque composant est pensé pour React et Tailwind. Copiez, adaptez et publiez en quelques minutes.',
            'label' => 'Fonctionnalités',
            'badge' => 'Badge',
            'image_url' => SectionAssets::shared(self::FEATURES_SHARED, 'saas-detail-1-1x1.png'),
            'image_alt' => 'Aperçu des blocs',
            'overlay_date' => '2025 | Mars',
            'overlay_title' => "Nouvelle\nCollection",
            'overlay_text' => 'Découvrez notre dernière série de composants soignés.',
            'overlay_link' => '#',
            'overlay_link_label' => 'Tout voir',
            'items' => self::featureCardItems(),
            'buttons' => self::primarySecondaryButtons('Parcourir les composants', 'Voir la démo'),
        ];

        return match ($variant) {
            'feature1', 'feature2' => array_merge($base, [
                'title' => 'Des blocs prêts à intégrer avec shadcn/ui',
                'subtitle' => 'Sections React prêtes pour la production, construites avec Tailwind et shadcn/ui.',
                'buttons' => [['label' => 'Voir la fonctionnalité', 'href' => '#', 'style' => 'secondary']],
            ]),
            'feature74' => array_merge($base, [
                'title' => 'Nom de la fonctionnalité',
                'subtitle' => 'Texte d\'introduction pour présenter la valeur de votre produit en quelques phrases.',
                'buttons' => [['label' => 'Réserver une démo', 'href' => '#', 'style' => 'primary']],
                'items' => array_slice(self::featureCardItems(), 0, 2),
            ]),
            'feature166' => array_merge($base, [
                'title' => 'Blocs construits avec Shadcn et Tailwind',
                'subtitle' => 'Composants soignés en React, Tailwind et shadcn/ui. Copiez et personnalisez directement dans votre projet.',
                'items' => self::bentoItems(),
            ]),
            'feature197' => array_merge($base, [
                'title' => 'Fonctionnalités',
                'items' => self::accordionItems(),
            ]),
            'feature239' => array_merge($base, [
                'title' => "Transformez une idée\nen réalité",
                'subtitle' => 'Libérez votre créativité dans un espace de travail intuitif. Imaginez, concevez et livrez sans friction.',
                'buttons' => [['label' => 'Parcourir les composants', 'href' => '#', 'style' => 'secondary']],
                'image_url' => SectionAssets::shared(self::FEATURES_SHARED, 'images/1-1x1.jpg'),
            ]),
            'feature51' => array_merge($base, [
                'items' => self::tabItems(),
            ]),
            default => $base,
        };
    }

    /**
     * @return array<string, mixed>
     */
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
     */
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
     */
    private static function contactContent(string $variant): array
    {
        return match ($variant) {
            'contact7' => [
                'title' => 'Nous contacter',
                'subtitle' => 'Une question ou besoin d\'aide ? Choisissez le canal qui vous convient.',
                'email_label' => 'Email',
                'email_description' => 'Nous répondons à tous les emails sous 24 heures.',
                'email' => 'contact@exemple.fr',
                'office_label' => 'Bureau',
                'office_description' => 'Passez nous voir pour échanger en personne.',
                'office_address' => '12 rue de la Paix, 75002 Paris',
                'office_href' => '#',
                'phone_label' => 'Téléphone',
                'phone_description' => 'Disponible du lundi au vendredi, 9 h à 18 h.',
                'phone' => '01 23 45 67 89',
                'chat_label' => 'Chat en direct',
                'chat_description' => 'Obtenez une réponse immédiate de notre équipe support.',
                'chat_link' => 'Démarrer le chat',
                'chat_href' => '#',
            ],
            default => [
                'title' => 'Contactez-nous',
                'subtitle' => 'Vous construisez avec des blocs prêts à l\'emploi ? Écrivez-nous pour choisir les sections adaptées à votre projet.',
                'phone' => '01 23 45 67 89',
                'email' => 'contact@exemple.fr',
                'web_label' => 'exemple.fr',
                'web_url' => 'https://www.exemple.fr',
                'form_heading' => 'Envoyez-nous un message',
                'form_subheading' => 'Nous répondons en général sous un jour ouvré.',
                'success_message' => 'Merci, votre message est bien arrivé.',
                'submit_label' => 'Envoyer le message',
                'submitting_label' => 'Envoi en cours…',
            ],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private static function testimonialsContent(string $variant): array
    {
        $items = self::testimonialItems();

        return match ($variant) {
            'testimonial8' => [
                'title' => 'Témoignages',
                'subtitle' => 'Découvrez ce que nos clients apprécient dans nos produits et services.',
                'items' => $items,
            ],
            'testimonial9' => [
                'title' => 'Témoignages',
                'subtitle' => 'Découvrez ce que nos clients apprécient dans nos produits et services.',
                'items' => array_slice($items, 0, 6),
            ],
            'testimonial10' => [
                'quote' => 'Cette bibliothèque de composants a transformé notre façon de livrer des interfaces. Nous gagnons un temps précieux sur chaque page.',
                'author_name' => 'Camille Dupont',
                'author_role' => 'Directrice produit',
                'author_avatar' => SectionAssets::shared('hero', 'avatars-webp/avatar-1.webp'),
            ],
            default => [
                'items' => self::testimonial4Items(),
            ],
        };
    }

    /**
     * @return list<array<string, string>>
     */
    private static function testimonial4Items(): array
    {
        $items = array_slice(self::testimonialItems(), 0, 4);
        if ($items !== []) {
            $items[0]['url'] = SectionAssets::shared('features', 'placeholder-1.svg');
        }

        return $items;
    }

    /**
     * @return list<array<string, string>>
     */
    private static function testimonialItems(): array
    {
        $entries = [
            ['Sophie Martin', 'Fondatrice et CEO', 'Cette bibliothèque a complètement transformé notre façon de construire des produits. Nous avons livré notre tableau de bord client deux fois plus vite.', 'avatars/avatar1.jpg', '#', 'x'],
            ['Lucas Bernard', 'CTO', 'L\'attention portée à l\'accessibilité et aux performances est remarquable. Nos scores Lighthouse ont progressé de 15 points après la migration.', 'avatars/avatar2.jpg', '#', 'linkedin'],
            ['Émilie Rousseau', 'Responsable produit', 'Enfin un design system que les développeurs adoptent volontiers. La documentation est claire et les composants flexibles.', 'avatars/avatar3.jpg', '#', 'x'],
            ['Thomas Petit', 'Lead technique', 'Nous avons comparé cinq bibliothèques avant de choisir celle-ci. L\'équilibre entre conventions et personnalisation nous a convaincus.', 'avatars/avatar4.jpg', '#', 'instagram'],
            ['Julie Moreau', 'Designer senior', 'Les composants correspondent fidèlement à nos maquettes Figma. La passation design vers développement n\'a jamais été aussi fluide.', 'avatars/avatar5.jpg', '#', 'facebook'],
            ['Nicolas Leroy', 'Développeur full stack', 'Le support TypeScript est excellent. L\'autocomplétion fonctionne, les types évitent les erreurs et l\'expérience développeur est agréable.', 'avatars-webp/avatar-3.webp', '#', 'x'],
            ['Nina Patel', 'UX engineer', 'Mode sombre, navigation clavier, lecteurs d\'écran : tout est prévu dès le départ. Nous passons moins de temps à corriger l\'accessibilité en fin de sprint.', 'avatars-webp/avatar-4.webp', '#', 'linkedin'],
            ['Alex Thompson', 'Engineering manager', 'La vélocité de l\'équipe a clairement augmenté. Moins de temps sur le boilerplate UI, plus de temps sur les fonctionnalités utiles.', 'avatars-webp/avatar-6.webp', '#', 'x'],
            ['Henry Garcia', 'Product lead', 'Nous avons reconstruit tout notre parcours d\'onboarding en moins de trois semaines. Le taux d\'activation a progressé de 20 % depuis la refonte.', 'avatars/avatar1.jpg', '#', 'instagram'],
        ];

        $items = [];
        foreach ($entries as [$name, $role, $text, $avatar, $href, $icon]) {
            $items[] = [
                'title' => $name,
                'label' => $role,
                'text' => $text,
                'url' => SectionAssets::shared('hero', $avatar),
                'href' => $href,
                'icon' => $icon,
            ];
        }

        return $items;
    }

    /**
     * @return list<array<string, string>>
     */
    private static function featureCardItems(): array
    {
        $titles = [
            ['Code source complet', 'Chaque bloc est du React que vous possédez. Aucune dépendance runtime.'],
            ['Design responsive', 'Adaptation fluide du mobile au desktop avec Tailwind.'],
            ['Personnalisable', 'Remplacez icônes, espacements et contenus sans verrouillage.'],
            ['Prêt pour la production', 'Code éprouvé, sans placeholder ni lorem ipsum.'],
            ['Compatible registry', 'Installation directe via la CLI shadcn.'],
            ['Framework agnostique', 'Fonctionne avec Next.js, Vite, Remix et Astro.'],
        ];
        $items = [];
        foreach ($titles as $i => [$title, $text]) {
            $items[] = [
                'title' => $title,
                'text' => $text,
                'label' => sprintf('%02d', $i + 1),
                'url' => SectionAssets::shared(self::FEATURES_SHARED, 'saas-card-detail-' . ($i + 1) . '-4x3.svg'),
                'href' => '#',
            ];
        }

        return $items;
    }

    /**
     * @return list<array<string, string>>
     */
    private static function bentoItems(): array
    {
        return [
            [
                'title' => 'UI/UX Design',
                'text' => 'Expériences intuitives avec des principes de design centrés utilisateur.',
                'url' => SectionAssets::shared(self::FEATURES_SHARED, 'placeholder-1.svg'),
            ],
            [
                'title' => 'Développement responsive',
                'text' => 'Sites qui s\'adaptent parfaitement à tous les écrans.',
                'url' => SectionAssets::shared(self::FEATURES_SHARED, 'placeholder-2.svg'),
            ],
            [
                'title' => 'Intégration de marque',
                'text' => 'Votre identité visuelle intégrée dans chaque détail.',
                'url' => SectionAssets::shared(self::FEATURES_SHARED, 'placeholder-3.svg'),
            ],
            [
                'title' => 'Optimisation performance',
                'text' => 'Chargement rapide grâce à un code et des assets optimisés.',
                'url' => SectionAssets::shared(self::FEATURES_SHARED, 'placeholder-4.svg'),
            ],
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private static function accordionItems(): array
    {
        $entries = [
            ['Blocs shadcn/ui', 'bw12.jpeg', 'Parcourez notre collection de blocs UI prêts à l\'emploi, responsive et accessibles.'],
            ['Tailwind et TypeScript', 'bw15.jpeg', 'Styling rapide et typage strict pour un code fiable en production.'],
            ['Mode sombre et personnalisation', 'bw20.jpeg', 'Chaque bloc supporte le dark mode et s\'adapte à votre thème.'],
            ['Accessibilité d\'abord', 'bw21.jpeg', 'ARIA, navigation clavier et HTML sémantique intégrés.'],
        ];
        $items = [];
        foreach ($entries as [$title, $file, $text]) {
            $items[] = [
                'title' => $title,
                'text' => $text,
                'url' => SectionAssets::shared(self::FEATURES_SHARED, 'lummi/' . $file),
            ];
        }

        return $items;
    }

    /**
     * @return list<array<string, string|bool>>
     */
    private static function tabItems(): array
    {
        return [
            [
                'title' => 'Recherche',
                'text' => 'Découvrez les fonctionnalités qui distinguent notre plateforme.',
                'url' => SectionAssets::shared(self::FEATURES_SHARED, 'placeholder-1.svg'),
                'href' => '#',
                'is_default' => true,
            ],
            [
                'title' => 'Affinage',
                'text' => 'Technologie récente pensée pour la productivité.',
                'url' => SectionAssets::shared(self::FEATURES_SHARED, 'placeholder-2.svg'),
                'href' => '#',
            ],
            [
                'title' => 'Construction',
                'text' => 'Créez des expériences avec notre boîte à outils complète.',
                'url' => SectionAssets::shared(self::FEATURES_SHARED, 'placeholder-3.svg'),
                'href' => '#',
            ],
        ];
    }
}
