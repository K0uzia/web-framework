<?php

declare(strict_types=1);

namespace App\Http\Dev\Sections\Defaults;

use Capsule\SectionAssets;

trait WaitlistDefaults
{
    private static function waitlistContent(string $variant): array
    {
        return match ($variant) {
            default => [
                'title' => 'Rejoignez la liste d\'attente',
                'subtitle' => 'Soyez parmi les premiers informés du lancement. Inscrivez-vous pour recevoir nos actualités et un accès anticipé.',
                'tagline' => '+ de 1 000 personnes déjà inscrites',
                'placeholder' => 'Votre adresse email',
                'submit_label' => 'Rejoindre la liste',
                'submitting_label' => 'Inscription en cours…',
                'success_message' => 'Merci, vous êtes bien inscrit(e) à la liste d\'attente.',
                'items' => self::waitlistAvatars(),
            ],
        };
    }

    /**
     * @return list<array<string, string>>
     */
    private static function waitlistAvatars(): array
    {
        $items = [];
        for ($i = 1; $i <= 6; $i++) {
            $items[] = [
                'url' => SectionAssets::shared('waitlist', 'avatars/avatar-' . $i . '.png'),
            ];
        }

        return $items;
    }
}
