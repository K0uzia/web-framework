<?php

declare(strict_types=1);

namespace App\Http\Dev\Sections\Defaults;

/**
 * Contenus par défaut des blocs d'inscription (signup1, signup2).
 */
trait SignupDefaults
{
    private static function signupContent(string $variant): array
    {
        $base = [
            'heading' => 'Créer un compte',
            'email_placeholder' => 'Email',
            'password_placeholder' => 'Mot de passe',
            'confirm_placeholder' => 'Confirmer le mot de passe',
            'email_label' => 'Email',
            'password_label' => 'Mot de passe',
            'confirm_label' => 'Confirmer le mot de passe',
            'button_text' => 'Créer mon compte',
            'login_text' => 'Déjà un compte ?',
            'login_link_label' => 'Se connecter',
            'logo_link' => '/',
            'logo_alt' => '',
            'logo_url' => '',
        ];

        return match ($variant) {
            'signup2' => array_merge($base, [
                'heading' => 'Rejoignez-nous',
            ]),
            default => $base,
        };
    }
}
