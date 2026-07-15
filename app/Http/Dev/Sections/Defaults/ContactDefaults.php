<?php

declare(strict_types=1);

namespace App\Http\Dev\Sections\Defaults;

use Capsule\SectionAssets;

trait ContactDefaults
{
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
     */}
