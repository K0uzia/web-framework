<?php

declare(strict_types=1);

namespace App\Http\Dev\Defaults;

use Capsule\SectionAssets;

trait TestimonialsDefaults
{
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
     * @return array<string, mixed>
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
     */}
