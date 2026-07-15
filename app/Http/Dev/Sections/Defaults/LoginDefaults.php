<?php

declare(strict_types=1);

namespace App\Http\Dev\Sections\Defaults;

/**
 * Contenus par défaut des blocs de connexion (login1, login2).
 */
trait LoginDefaults
{
    private static function loginContent(string $variant): array
    {
        $base = [
            'heading' => 'Connexion',
            'email_placeholder' => 'Email',
            'password_placeholder' => 'Mot de passe',
            'email_label' => 'Email',
            'password_label' => 'Mot de passe',
            'button_text' => 'Se connecter',
            'forgot_password_label' => 'Mot de passe oublié ?',
            'forgot_password_url' => '',
            'forgot_heading' => 'Mot de passe oublié',
            'forgot_description' => 'Saisissez votre adresse email. Nous vous enverrons un lien de réinitialisation.',
            'forgot_button_text' => 'Envoyer le lien',
            'forgot_back_label' => 'Retour à la connexion',
            'signup_text' => 'Pas encore de compte ?',
            'signup_link_label' => 'Créer un compte',
            'logo_link' => '/',
            'logo_alt' => '',
            'logo_url' => '',
        ];

        return match ($variant) {
            'login2' => array_merge($base, [
                'heading' => 'Connexion à votre espace',
            ]),
            default => $base,
        };
    }
}
