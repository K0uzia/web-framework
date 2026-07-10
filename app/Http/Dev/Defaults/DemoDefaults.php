<?php

declare(strict_types=1);

namespace App\Http\Dev\Defaults;

use Capsule\SectionAssets;

trait DemoDefaults
{
    private static function demoContent(string $variant): array
    {
        return match ($variant) {
            'bookademo2' => self::bookademo2Content(),
            default => self::bookademo1Content(),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private static function bookademo1Content(): array
    {
        return [
            'tagline' => 'Pour commencer',
            'title' => 'Simplifiez votre flux de développement',
            'items' => self::bookademo1Benefits(),
            'logos' => self::demoLogos(8),
            'submit_label' => 'Envoyer',
            'submitting_label' => 'Envoi en cours…',
            'success_message' => 'Merci, votre demande de démo a bien été enregistrée.',
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private static function bookademo1Benefits(): array
    {
        return [
            [
                'text' => 'Rejoignez des milliers de développeurs qui utilisent notre plateforme.',
            ],
            [
                'text' => 'Obtenez une démo personnalisée selon vos besoins.',
            ],
            [
                'text' => 'Découvrez comment accélérer vos livraisons sans sacrifier la qualité.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function bookademo2Content(): array
    {
        return [
            'title' => 'Planifier une démo',
            'text' => 'Réservez une démo pour explorer notre plateforme de développement et découvrir comment elle peut accélérer la productivité de votre équipe. Pour des questions techniques, contactez notre équipe.',
            'link_label' => 'contactez notre équipe',
            'href' => '#',
            'avatar1_url' => SectionAssets::shared('demo', 'portraits/avatar-2.jpg'),
            'avatar2_url' => SectionAssets::shared('demo', 'portraits/avatar-1.jpg'),
            'items' => self::bookademo2Testimonials(),
            'footer_title' => 'Plébiscité par les équipes de développement du monde entier',
            'logos' => self::demoLogos(10),
            'submit_label' => 'Continuer',
            'submitting_label' => 'Envoi en cours…',
            'success_message' => 'Merci, votre demande de démo a bien été enregistrée.',
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private static function bookademo2Testimonials(): array
    {
        $shared = 'demo';

        return [
            [
                'text' => 'Cette plateforme a révolutionné notre flux de développement. Les gains de productivité ont été incroyables.',
                'label' => 'révolutionné, productivité, gains, incroyables',
                'author' => 'Alex Chen',
                'role' => 'Développeur principal',
                'logo' => SectionAssets::shared($shared, 'logos/fictional-company-logo-1.svg'),
                'url' => SectionAssets::shared($shared, 'portraits/avatar-3.jpg'),
            ],
            [
                'text' => 'L\'intégration a été fluide et notre équipe était opérationnelle en quelques minutes. Un vrai changement pour notre startup.',
                'label' => 'fluide, minutes, changement',
                'author' => 'Marcus Rodriguez',
                'role' => 'CTO',
                'logo' => SectionAssets::shared($shared, 'logos/fictional-company-logo-2.svg'),
                'url' => SectionAssets::shared($shared, 'portraits/avatar-2.jpg'),
            ],
            [
                'text' => 'Nous avons réduit notre temps de développement de 40 % depuis l\'adoption de cette solution. Je la recommande vivement.',
                'label' => 'réduit, 40 %, recommande',
                'author' => 'Emily Watson',
                'role' => 'Responsable ingénierie',
                'logo' => SectionAssets::shared($shared, 'logos/fictional-company-logo-3.svg'),
                'url' => SectionAssets::shared($shared, 'portraits/avatar-4.jpg'),
            ],
            [
                'text' => 'L\'expérience développeur est remarquable. L\'adoption par l\'équipe a été instantanée et la courbe d\'apprentissage minimale.',
                'label' => 'remarquable, instantanée, minimale',
                'author' => 'David Kim',
                'role' => 'Développeur senior',
                'logo' => SectionAssets::shared($shared, 'logos/fictional-company-logo-4.svg'),
                'url' => SectionAssets::shared($shared, 'portraits/avatar-5.jpg'),
            ],
            [
                'text' => 'Nous avons constaté une amélioration de 60 % de la fréquence de déploiement depuis le passage à cette plateforme. Absolument transformant.',
                'label' => '60 %, amélioration, transformant',
                'author' => 'Lisa Thompson',
                'role' => 'Ingénieure DevOps',
                'logo' => SectionAssets::shared($shared, 'logos/fictional-company-logo-5.svg'),
                'url' => SectionAssets::shared($shared, 'portraits/avatar-6.jpg'),
            ],
            [
                'text' => 'Les fonctionnalités de collaboration ont transformé la façon dont notre équipe distante travaille ensemble. Comme si toute l\'équipe était dans la même pièce.',
                'label' => 'transformé, collaboration, ensemble',
                'author' => 'James Wilson',
                'role' => 'Chef de produit',
                'logo' => SectionAssets::shared($shared, 'logos/fictional-company-logo-6.svg'),
                'url' => SectionAssets::shared($shared, 'portraits/avatar-1.jpg'),
            ],
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private static function demoLogos(int $count): array
    {
        $logos = [];
        for ($i = 1; $i <= $count; $i++) {
            $logos[] = [
                'url' => SectionAssets::shared('demo', 'logos/fictional-company-logo-' . $i . '.svg'),
            ];
        }

        return $logos;
    }
}
