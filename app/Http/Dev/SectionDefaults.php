<?php

declare(strict_types=1);

namespace App\Http\Dev;

use Capsule\StockImages;

/**
 * Contenus et styles par défaut des blocs, utilisés à l'ajout d'un bloc
 * et par les modèles de pages.
 */
final class SectionDefaults
{
    /**
     * @return array<string, mixed>
     */
    public static function content(string $type, string $variant = ''): array
    {
        if ($type === 'hero' && $variant !== '') {
            return self::heroContent($variant);
        }

        return match ($type) {
            'hero' => self::heroContent('centered'),
            'features' => [
                'title' => 'Fonctionnalités',
                'subtitle' => 'Tout ce qu\'il faut pour lancer, publier et faire évoluer votre site.',
                'items' => [
                    ['title' => 'Mise en page modulaire', 'text' => 'Composez vos pages avec des blocs prêts à l\'emploi, sans code.'],
                    ['title' => 'Performance intégrée', 'text' => 'CSS léger, polices locales et structure pensée pour le web.'],
                    ['title' => 'Édition intuitive', 'text' => 'Modifiez textes, images et navigation depuis un tableau de bord clair.'],
                ],
            ],
            'cta' => [
                'title' => 'Prêt à publier votre prochain site ?',
                'subtitle' => 'Créez une page, choisissez un modèle et personnalisez le thème en quelques minutes.',
                'buttons' => [
                    ['label' => 'Commencer', 'href' => '#', 'style' => 'primary'],
                    ['label' => 'Voir les modèles', 'href' => '#', 'style' => 'secondary'],
                ],
            ],
            'pricing' => [
                'title' => 'Tarifs',
                'subtitle' => 'Choisissez la formule adaptée à vos besoins.',
                'items' => [
                    [
                        'title' => 'Découverte',
                        'price' => '0 €',
                        'period' => '/mois',
                        'text' => 'Pour essayer sans engagement.',
                        'list' => "1 projet\nSupport communautaire",
                        'cta_label' => 'Choisir',
                        'cta_href' => '#',
                    ],
                    [
                        'title' => 'Pro',
                        'price' => '19 €',
                        'period' => '/mois',
                        'text' => 'Pour les équipes qui avancent.',
                        'list' => "Projets illimités\nSupport prioritaire\nStatistiques",
                        'cta_label' => 'Choisir',
                        'cta_href' => '#',
                    ],
                    [
                        'title' => 'Entreprise',
                        'price' => 'Sur devis',
                        'period' => '',
                        'text' => 'Accompagnement sur mesure.',
                        'list' => "Tout le plan Pro\nSLA dédié\nIntégrations avancées",
                        'cta_label' => 'Nous contacter',
                        'cta_href' => '#',
                    ],
                ],
            ],
            'testimonials' => [
                'title' => 'Ils nous font confiance',
                'subtitle' => 'Des retours concrets de clients et d\'équipes qui utilisent le produit au quotidien.',
                'items' => [
                    ['text' => 'Nous avons lancé notre site en une journée, avec une identité visuelle cohérente dès le départ.', 'title' => 'Camille Martin', 'role' => 'Fondatrice, Atelier Nova'],
                    ['text' => 'L\'éditeur est clair, les blocs sont beaux par défaut, et les mises à jour sont rapides.', 'title' => 'Thomas Leroy', 'role' => 'Responsable marketing, Boreal'],
                    ['text' => 'Enfin un outil simple pour notre équipe, sans compromis sur l\'accessibilité.', 'title' => 'Inès Dupont', 'role' => 'Lead design, Collectif Ouest'],
                ],
            ],
            'faq' => [
                'title' => 'Questions fréquentes',
                'subtitle' => '',
                'items' => [
                    ['title' => 'Comment démarrer ?', 'text' => 'Créez un compte et suivez le guide de prise en main.'],
                    ['title' => 'Puis-je annuler à tout moment ?', 'text' => 'Oui, sans frais ni condition.'],
                    ['title' => 'Proposez-vous un essai gratuit ?', 'text' => 'Oui, la formule Découverte est gratuite.'],
                ],
            ],
            'stats' => [
                'title' => '',
                'items' => [
                    ['value' => '99 %', 'title' => 'Satisfaction client'],
                    ['value' => '10 k+', 'title' => 'Utilisateurs actifs'],
                    ['value' => '24/7', 'title' => 'Disponibilité'],
                ],
            ],
            'logos' => [
                'title' => 'Ils travaillent avec nous',
                'items' => [
                    ['title' => 'Entreprise A'],
                    ['title' => 'Entreprise B'],
                    ['title' => 'Entreprise C'],
                    ['title' => 'Entreprise D'],
                ],
            ],
            'team' => [
                'title' => 'Notre équipe',
                'subtitle' => 'Des profils complémentaires au service de votre projet.',
                'items' => [
                    ['title' => 'Camille Martin', 'role' => 'Fondatrice', 'text' => 'Stratégie produit et direction artistique.'],
                    ['title' => 'Thomas Leroy', 'role' => 'Développement', 'text' => 'Architecture technique et performance web.'],
                    ['title' => 'Inès Dupont', 'role' => 'Design', 'text' => 'Interfaces accessibles et systèmes visuels cohérents.'],
                ],
            ],
            'contact' => [
                'title' => 'Contactez-nous',
                'subtitle' => 'Nous répondons sous 24 h ouvrées.',
                'items' => [
                    ['title' => 'Email', 'text' => 'contact@exemple.fr', 'href' => 'mailto:contact@exemple.fr'],
                    ['title' => 'Téléphone', 'text' => '01 23 45 67 89', 'href' => 'tel:+33123456789'],
                    ['title' => 'Adresse', 'text' => '1 rue de l\'Exemple, 75000 Paris', 'href' => ''],
                ],
            ],
            'content' => [
                'title' => 'Titre de section',
                'text' => 'Votre texte ici. Chaque ligne devient un paragraphe.',
            ],
            'about' => [
                'title' => 'Notre histoire',
                'subtitle' => 'Ce qui nous anime au quotidien.',
                'text' => "Nous construisons des outils simples pour les équipes qui veulent aller vite.\nNotre approche : clarté, qualité et respect de l'utilisateur.",
                'items' => [
                    ['title' => 'Mission', 'text' => 'Rendre la création web accessible à tous.'],
                    ['title' => 'Vision', 'text' => 'Un site composable, rapide et facile à maintenir.'],
                    ['title' => 'Valeurs', 'text' => 'Transparence, simplicité et écoute client.'],
                ],
            ],
            'steps' => [
                'title' => 'Comment ça marche',
                'subtitle' => 'Trois étapes pour démarrer.',
                'items' => [
                    ['title' => 'Créer', 'text' => 'Choisissez un modèle et personnalisez vos blocs.'],
                    ['title' => 'Publier', 'text' => 'Mettez votre page en ligne en un clic.'],
                    ['title' => 'Mesurer', 'text' => 'Suivez les résultats et itérez.'],
                ],
            ],
            'banner' => [
                'text' => 'Nouveau : découvrez notre dernière fonctionnalité.',
                'href' => '#',
                'link_label' => 'En savoir plus',
            ],
            'compare' => [
                'title' => 'Pourquoi nous choisir',
                'subtitle' => 'Comparaison avec une approche classique.',
                'col_a_label' => 'Avec nous',
                'col_b_label' => 'Sans nous',
                'items' => [
                    ['title' => 'Mise en ligne', 'text' => 'Quelques minutes', 'role' => 'Plusieurs semaines'],
                    ['title' => 'Maintenance', 'text' => 'Interface visuelle', 'role' => 'Code à modifier'],
                    ['title' => 'Coût', 'text' => 'Prévisible', 'role' => 'Développement sur mesure'],
                ],
            ],
            'gallery' => [
                'title' => 'Galerie',
                'subtitle' => 'Quelques visuels de nos réalisations.',
                'items' => [
                    ['url' => StockImages::gallery(0), 'title' => 'Espace de travail'],
                    ['url' => StockImages::gallery(1), 'title' => 'Architecture'],
                    ['url' => StockImages::gallery(2), 'title' => 'Équipe en action'],
                    ['url' => StockImages::gallery(3), 'title' => 'Collaboration'],
                    ['url' => StockImages::gallery(4), 'title' => 'Bureau moderne'],
                    ['url' => StockImages::gallery(5), 'title' => 'Réunion projet'],
                ],
            ],
            'newsletter' => [
                'title' => 'Restez informé',
                'subtitle' => 'Recevez nos nouveautés et conseils, sans spam.',
                'input_hint' => 'votre@email.fr',
                'buttons' => [
                    ['label' => 'S\'inscrire', 'href' => '#', 'style' => 'primary'],
                ],
            ],
            'integrations' => self::itemsSection('Intégrations', 'Compatible avec vos outils du quotidien.', [
                ['title' => 'Slack', 'text' => 'Notifications en temps réel.'],
                ['title' => 'Notion', 'text' => 'Synchronisation des pages.'],
                ['title' => 'Zapier', 'text' => 'Automatisations sans code.'],
            ]),
            'blog' => self::itemsSection('Articles', 'Dernières publications et retours d\'expérience.', [
                ['title' => 'Comment démarrer en 30 minutes', 'text' => 'Guide pas à pas pour lancer votre site, choisir un modèle et publier.', 'href' => '#', 'role' => 'Guide', 'url' => StockImages::blog(0)],
                ['title' => 'Bonnes pratiques SEO en 2026', 'text' => 'Titres, descriptions, structure et performance : l\'essentiel pour être visible.', 'href' => '#', 'role' => 'SEO', 'url' => StockImages::blog(1)],
                ['title' => 'Nouveautés produit du mois', 'text' => 'Blocs, thème et navigation : ce qui change pour vos prochaines pages.', 'href' => '#', 'role' => 'Produit', 'url' => StockImages::blog(2)],
            ]),
            'projects' => [
                'title' => 'Nos projets',
                'subtitle' => 'Des réalisations récentes, du concept à la mise en ligne.',
                'items' => [
                    ['url' => StockImages::project(0), 'title' => 'Plateforme Nova', 'text' => 'Site vitrine et blog pour une startup B2B.', 'href' => '#', 'role' => 'Site vitrine'],
                    ['url' => StockImages::project(1), 'title' => 'Refonte Atelier Ouest', 'text' => 'Identité visuelle et pages marketing pour un collectif design.', 'href' => '#', 'role' => 'Refonte'],
                    ['url' => StockImages::project(2), 'title' => 'Portail Boreal', 'text' => 'Landing produit, tarifs et documentation intégrée.', 'href' => '#', 'role' => 'Landing'],
                ],
            ],
            'timeline' => self::itemsSection('Chronologie', '', [
                ['title' => 'Lancement', 'text' => 'Première version publique.', 'role' => '2024'],
                ['title' => 'Croissance', 'text' => '10 000 utilisateurs actifs.', 'role' => '2025'],
                ['title' => 'Aujourd\'hui', 'text' => 'Nouvelle interface et API.', 'role' => '2026'],
            ]),
            'services' => self::itemsSection('Services', 'Ce que nous proposons.', [
                ['title' => 'Conseil', 'text' => 'Accompagnement stratégique.'],
                ['title' => 'Design', 'text' => 'Interfaces claires et accessibles.'],
                ['title' => 'Développement', 'text' => 'Sites rapides et maintenables.'],
            ]),
            'login' => [
                'title' => 'Connexion',
                'subtitle' => 'Accédez à votre espace.',
                'input_hint' => 'votre@email.fr',
                'buttons' => [['label' => 'Se connecter', 'href' => '#', 'style' => 'primary']],
            ],
            'signup' => [
                'title' => 'Créer un compte',
                'subtitle' => 'Gratuit, sans carte bancaire.',
                'input_hint' => 'votre@email.fr',
                'buttons' => [['label' => 'S\'inscrire', 'href' => '#', 'style' => 'primary']],
            ],
            'careers' => self::itemsSection('Carrières', 'Rejoignez l\'équipe.', [
                ['title' => 'Développeur PHP', 'text' => 'Temps plein, télétravail possible.', 'href' => '#'],
                ['title' => 'Designer produit', 'text' => 'CDI, Paris.', 'href' => '#'],
            ]),
            'compliance' => self::itemsSection('Conformité', '', [
                ['title' => 'RGPD', 'text' => 'Données hébergées en UE.'],
                ['title' => 'Accessibilité', 'text' => 'Objectif WCAG AA.'],
            ]),
            'case-studies' => self::itemsSection('Études de cas', 'Résultats concrets.', [
                ['title' => 'Startup A', 'text' => '+40 % de conversion en 3 mois.', 'href' => '#'],
                ['title' => 'Agence B', 'text' => 'Livraison 2x plus rapide.', 'href' => '#'],
            ]),
            'changelog' => self::itemsSection('Journal des versions', '', [
                ['title' => 'v2.0', 'text' => 'Nouveau catalogue de blocs.', 'role' => 'Juil. 2026'],
                ['title' => 'v1.5', 'text' => 'Éditeur de pages amélioré.', 'role' => 'Mai 2026'],
            ]),
            'community' => self::itemsSection('Communauté', 'Échangez avec nous.', [
                ['title' => 'Forum', 'text' => 'Questions et entraide.', 'href' => '#'],
                ['title' => 'Discord', 'text' => 'Salon en direct.', 'href' => '#'],
            ]),
            'download' => self::itemsSection('Téléchargements', '', [
                ['title' => 'Guide PDF', 'text' => 'Documentation complète.', 'href' => '#'],
                ['title' => 'Kit média', 'text' => 'Logos et visuels.', 'href' => '#'],
            ]),
            'industries' => self::itemsSection('Secteurs', 'Nous accompagnons.', [
                ['title' => 'SaaS', 'text' => 'Pages produit et onboarding.'],
                ['title' => 'E-commerce', 'text' => 'Vitrines et lancements.'],
                ['title' => 'Éducation', 'text' => 'Sites institutionnels.'],
            ]),
            'highlights' => self::itemsSection('Points forts', '', [
                ['title' => 'Rapide', 'text' => 'Mise en ligne en minutes.'],
                ['title' => 'Flexible', 'text' => 'Blocs composables.'],
                ['title' => 'Sécurisé', 'text' => 'Bonnes pratiques intégrées.'],
            ]),
            'experience' => self::itemsSection('Parcours', '', [
                ['title' => 'Lead développeur', 'text' => 'Architecture et mentoring.', 'role' => '2022 - aujourd\'hui'],
                ['title' => 'Développeur full stack', 'text' => 'Applications web.', 'role' => '2018 - 2022'],
            ]),
            'process' => self::itemsSection('Processus', 'Notre méthode.', [
                ['title' => 'Découverte', 'text' => 'Comprendre vos objectifs.'],
                ['title' => 'Conception', 'text' => 'Maquettes et contenus.'],
                ['title' => 'Livraison', 'text' => 'Mise en ligne et suivi.'],
            ]),
            'waitlist' => [
                'title' => 'Rejoignez la liste d\'attente',
                'subtitle' => 'Soyez parmi les premiers informés.',
                'input_hint' => 'votre@email.fr',
                'buttons' => [['label' => 'M\'inscrire', 'href' => '#', 'style' => 'primary']],
            ],
            'awards' => self::itemsSection('Récompenses', '', [
                ['title' => 'Prix Innovation 2025', 'text' => 'Catégorie outils créateurs.'],
                ['title' => 'Top Product', 'text' => 'Communauté indie hackers.'],
            ]),
            'resources' => self::itemsSection('Ressources', 'Guides et outils.', [
                ['title' => 'Documentation', 'text' => 'Référence complète.', 'href' => '#'],
                ['title' => 'Tutoriels', 'text' => 'Vidéos pas à pas.', 'href' => '#'],
            ]),
            'code' => [
                'title' => 'Exemple de code',
                'subtitle' => 'Intégration minimale.',
                'code' => "<?php\n\$page = \$renderer->renderBySlug('accueil');\necho \$page->getBody();",
            ],
            'demo' => [
                'title' => 'Réserver une démo',
                'subtitle' => '30 minutes pour découvrir le produit.',
                'image_url' => StockImages::product(0),
                'buttons' => [['label' => 'Choisir un créneau', 'href' => '#', 'style' => 'primary']],
            ],
            'ui-announcement' => [
                'text' => 'Nouveau : découvrez notre dernière fonctionnalité.',
                'href' => '#',
                'link_label' => 'En savoir plus',
            ],
            'ui-divider' => [],
            'ui-quote' => [
                'title' => 'La simplicité est la sophistication suprême.',
                'subtitle' => 'Leonardo da Vinci',
            ],
            'ui-rating' => [
                'title' => '4.9/5',
                'subtitle' => 'Basé sur 200+ avis',
            ],
            'ui-embed' => [
                'title' => 'Aperçu vidéo',
                'image_url' => StockImages::product(1),
            ],
            'ui-card' => [
                'title' => 'Titre de la carte',
                'subtitle' => 'Texte descriptif court pour ce composant.',
                'buttons' => [['label' => 'Action', 'href' => '#', 'style' => 'primary']],
            ],
            default => [],
        };
    }

    /**
     * @return array<string, string>
     */
    public static function style(string $type): array
    {
        return match ($type) {
            'hero' => ['bg' => 'primary', 'text_align' => 'center', 'padding' => 'xl'],
            'features' => ['bg' => 'muted', 'padding' => 'lg'],
            'cta' => ['bg' => 'primary', 'padding' => 'lg'],
            'pricing' => ['bg' => 'background', 'padding' => 'lg'],
            'testimonials' => ['bg' => 'muted', 'padding' => 'lg'],
            'faq' => ['bg' => 'background', 'padding' => 'lg'],
            'stats' => ['bg' => 'primary', 'padding' => 'md'],
            'logos' => ['bg' => 'background', 'padding' => 'sm'],
            'team' => ['bg' => 'background', 'padding' => 'lg'],
            'contact' => ['bg' => 'muted', 'padding' => 'lg'],
            'content' => ['bg' => 'background', 'text_align' => 'left', 'padding' => 'lg'],
            'about' => ['bg' => 'background', 'padding' => 'lg'],
            'steps' => ['bg' => 'background', 'padding' => 'lg'],
            'banner' => ['bg' => 'primary', 'padding' => 'sm'],
            'compare' => ['bg' => 'muted', 'padding' => 'lg'],
            'gallery' => ['bg' => 'background', 'padding' => 'lg'],
            'newsletter' => ['bg' => 'muted', 'padding' => 'lg'],
            'integrations' => ['bg' => 'background', 'padding' => 'md'],
            'blog' => ['bg' => 'background', 'padding' => 'lg'],
            'projects' => ['bg' => 'muted', 'padding' => 'lg'],
            'timeline' => ['bg' => 'background', 'padding' => 'lg'],
            'services' => ['bg' => 'muted', 'padding' => 'lg'],
            'login' => ['bg' => 'background', 'padding' => 'lg'],
            'signup' => ['bg' => 'muted', 'padding' => 'lg'],
            'careers' => ['bg' => 'background', 'padding' => 'lg'],
            'compliance' => ['bg' => 'muted', 'padding' => 'md'],
            'case-studies' => ['bg' => 'background', 'padding' => 'lg'],
            'changelog' => ['bg' => 'background', 'padding' => 'lg'],
            'community' => ['bg' => 'muted', 'padding' => 'lg'],
            'download' => ['bg' => 'background', 'padding' => 'lg'],
            'industries' => ['bg' => 'muted', 'padding' => 'lg'],
            'highlights' => ['bg' => 'background', 'padding' => 'lg'],
            'experience' => ['bg' => 'background', 'padding' => 'lg'],
            'process' => ['bg' => 'muted', 'padding' => 'lg'],
            'waitlist' => ['bg' => 'primary', 'padding' => 'lg'],
            'awards' => ['bg' => 'background', 'padding' => 'md'],
            'resources' => ['bg' => 'muted', 'padding' => 'lg'],
            'code' => ['bg' => 'muted', 'padding' => 'lg'],
            'demo' => ['bg' => 'primary', 'text_align' => 'center', 'padding' => 'lg'],
            'ui-announcement' => ['bg' => 'primary', 'padding' => 'sm'],
            'ui-divider' => ['bg' => 'background', 'padding' => 'sm'],
            'ui-quote' => ['bg' => 'muted', 'padding' => 'md'],
            'ui-rating' => ['bg' => 'background', 'padding' => 'md'],
            'ui-embed' => ['bg' => 'background', 'padding' => 'lg'],
            'ui-card' => ['bg' => 'muted', 'padding' => 'md'],
            default => ['padding' => 'md'],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private static function heroContent(string $variant): array
    {
        $base = [
            'title' => 'Votre titre principal',
            'subtitle' => 'Une phrase d\'accroche qui résume votre proposition de valeur.',
            'buttons' => [
                ['label' => 'Commencer', 'href' => '#', 'style' => 'primary'],
            ],
            'badge' => '',
            'image_url' => '',
        ];

        return match ($variant) {
            'badge' => array_merge($base, [
                'badge' => 'Nouveau',
                'title' => 'Lancez votre projet plus vite',
                'subtitle' => 'Une solution clé en main pour aller à l\'essentiel.',
            ]),
            'fullscreen' => array_merge($base, [
                'title' => 'Construisez quelque chose d\'exceptionnel',
                'subtitle' => 'Un hero plein écran pour capter l\'attention dès la première seconde.',
                'buttons' => [
                    ['label' => 'Découvrir', 'href' => '#', 'style' => 'primary'],
                    ['label' => 'En savoir plus', 'href' => '#', 'style' => 'secondary'],
                ],
            ]),
            'split', 'split-left', 'image-below' => array_merge($base, [
                'title' => 'Un produit pensé pour vous',
                'subtitle' => 'Texte d\'accroche avec visuel pour illustrer votre proposition.',
                'image_url' => StockImages::hero(0),
            ]),
            'video' => array_merge($base, [
                'title' => 'Découvrez le produit en action',
                'subtitle' => 'Une démonstration claire pour convaincre dès les premières secondes.',
                'image_url' => StockImages::hero(1),
                'video_url' => '',
            ]),
            default => $base,
        };
    }

    /**
     * @param list<array<string, string>> $items
     *
     * @return array<string, mixed>
     */
    private static function itemsSection(string $title, string $subtitle, array $items): array
    {
        return [
            'title' => $title,
            'subtitle' => $subtitle,
            'items' => $items,
        ];
    }
}
