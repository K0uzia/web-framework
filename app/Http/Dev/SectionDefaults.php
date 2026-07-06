<?php

declare(strict_types=1);

namespace App\Http\Dev;

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
                'subtitle' => '',
                'items' => [
                    ['title' => 'Point 1', 'text' => 'Description courte.'],
                    ['title' => 'Point 2', 'text' => 'Description courte.'],
                    ['title' => 'Point 3', 'text' => 'Description courte.'],
                ],
            ],
            'cta' => [
                'title' => 'Prêt à vous lancer ?',
                'buttons' => [
                    ['label' => 'Commencer', 'href' => '#', 'style' => 'primary'],
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
                'subtitle' => '',
                'items' => [
                    ['text' => 'Un outil qui a changé notre façon de travailler.', 'title' => 'Prénom Nom', 'role' => 'Rôle, Entreprise'],
                    ['text' => 'Simple, rapide et efficace. Je recommande.', 'title' => 'Prénom Nom', 'role' => 'Rôle, Entreprise'],
                    ['text' => 'Le support est réactif et le produit évolue vite.', 'title' => 'Prénom Nom', 'role' => 'Rôle, Entreprise'],
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
                'subtitle' => '',
                'items' => [
                    ['title' => 'Prénom Nom', 'role' => 'Fondateur', 'text' => ''],
                    ['title' => 'Prénom Nom', 'role' => 'Design', 'text' => ''],
                    ['title' => 'Prénom Nom', 'role' => 'Développement', 'text' => ''],
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
                    ['url' => '', 'title' => 'Projet A'],
                    ['url' => '', 'title' => 'Projet B'],
                    ['url' => '', 'title' => 'Projet C'],
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
            'blog' => self::itemsSection('Articles', 'Dernières publications.', [
                ['title' => 'Comment démarrer', 'text' => 'Guide pas à pas pour lancer votre site.', 'href' => '#'],
                ['title' => 'Bonnes pratiques SEO', 'text' => 'Optimiser titres, descriptions et structure.', 'href' => '#'],
                ['title' => 'Mise à jour produit', 'text' => 'Nouveautés du mois.', 'href' => '#'],
            ]),
            'projects' => [
                'title' => 'Nos projets',
                'subtitle' => 'Quelques réalisations récentes.',
                'items' => [
                    ['url' => '', 'title' => 'Projet Alpha', 'href' => '#'],
                    ['url' => '', 'title' => 'Projet Beta', 'href' => '#'],
                    ['url' => '', 'title' => 'Projet Gamma', 'href' => '#'],
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
                'buttons' => [['label' => 'Choisir un créneau', 'href' => '#', 'style' => 'primary']],
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
