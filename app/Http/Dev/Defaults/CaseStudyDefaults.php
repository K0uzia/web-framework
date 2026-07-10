<?php

declare(strict_types=1);

namespace App\Http\Dev\Defaults;

use Capsule\SectionAssets;

trait CaseStudyDefaults
{
    private static function caseStudyContent(string $variant): array
    {
        return match ($variant) {
            'casestudies3' => self::caseStudy3Content(),
            default => self::caseStudy2Content(),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private static function caseStudy2Content(): array
    {
        return [
            'tagline' => '+ de 4 500 clients satisfaits',
            'title' => 'Des résultats concrets, chez de vrais utilisateurs',
            'items' => self::caseStudy2Items(),
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private static function caseStudy2Items(): array
    {
        $shared = 'case-study';

        return [
            [
                'url' => SectionAssets::shared($shared, 'placeholder-1.svg'),
                'text' => 'Cet outil de productivité a transformé notre façon de collaborer. Le flux de travail de l\'équipe s\'est nettement amélioré et nous avons réduit de moitié le temps en réunion tout en augmentant la production.',
                'author' => 'Michael Rivera',
                'role' => 'Directeur produit',
                'logo' => SectionAssets::shared($shared, 'logos/fictional-company-logo-2.svg'),
                'stat1_value' => '98 %',
                'stat1_label' => 'Satisfaction client',
                'stat1_text' => 'D\'après les avis vérifiés',
                'stat2_value' => '3,8x',
                'stat2_label' => 'Amélioration du ROI',
                'stat2_text' => 'Dès le premier trimestre',
            ],
            [
                'url' => SectionAssets::shared($shared, 'placeholder-2.svg'),
                'text' => 'L\'interface est intuitive et adaptable à nos besoins. Nous l\'avons déployée dans tous les services avec peu de formation et les résultats ont été immédiats.',
                'author' => 'Sarah Chen',
                'role' => 'Responsable opérations',
                'logo' => SectionAssets::shared($shared, 'logos/fictional-company-logo-3.svg'),
                'stat1_value' => '4,2x',
                'stat1_label' => 'Efficacité d\'équipe',
                'stat1_text' => 'Gains de productivité mesurés',
                'stat2_value' => '72 %',
                'stat2_label' => 'Temps de traitement réduit',
                'stat2_text' => 'Sur l\'ensemble des projets',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function caseStudy3Content(): array
    {
        $shared = 'case-study';

        return [
            'read_more_label' => 'Lire l\'étude de cas',
            'featured_logo' => SectionAssets::shared($shared, 'block-1.svg'),
            'featured_company' => 'Acme',
            'featured_tags' => 'INTELLIGENCE ARTIFICIELLE / SOLUTIONS ENTREPRISE',
            'featured_title' => 'Automatisation des flux pour l\'ère numérique.',
            'featured_subtitle' => 'Comment automatiser vos processus avec l\'IA.',
            'featured_url' => SectionAssets::shared($shared, 'placeholder-1.svg'),
            'featured_href' => '#',
            'items' => [
                [
                    'logo' => SectionAssets::shared($shared, 'block-2.svg'),
                    'company' => 'Super',
                    'tags' => 'MIGRATION DE DONNÉES / SOLUTIONS LOGICIELLES',
                    'title' => 'Optimiser la migration de données avec l\'IA.',
                    'subtitle' => 'Une plateforme de migration vers un avenir data-driven.',
                    'href' => '#',
                ],
                [
                    'logo' => SectionAssets::shared($shared, 'block-3.svg'),
                    'company' => 'Advent',
                    'tags' => 'INTELLIGENCE ARTIFICIELLE / SOLUTIONS DATA',
                    'title' => 'Une stratégie IA pour une entreprise pérenne.',
                    'subtitle' => 'Maîtriser l\'IA pour des opérations plus efficaces.',
                    'href' => '#',
                ],
            ],
        ];
    }
}
