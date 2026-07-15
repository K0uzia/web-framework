<?php

declare(strict_types=1);

namespace Capsule\Section\Signup;

/**
 * Rendu HTML des variantes signup (shadcnblocks signup1, signup2).
 */
final class SignupVariantRenderer
{
    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    public static function enrich(array $data, array $content, string $variant): array
    {
        $variant = SignupStyle::normalizeVariant($variant);
        $data['signup_logo_html'] = self::logoHtml($content, $variant);
        $data['signup_form_html'] = self::formHtml($content, $variant);
        $data['signup_login_html'] = self::loginSwitchHtml($content, $variant);

        return $data;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function logoHtml(array $content, string $variant): string
    {
        $url = trim((string) ($content['logo_url'] ?? ''));
        $home = trim((string) ($content['logo_link'] ?? '/'));
        $alt = trim((string) ($content['logo_alt'] ?? ''));
        if ($alt === '') {
            $alt = trim((string) ($content['heading'] ?? 'Accueil'));
        }
        if ($url === '') {
            return '';
        }

        return '<a class="section-signup__logo-link--' . $variant . '" href="'
            . htmlspecialchars($home !== '' ? $home : '/', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">'
            . '<img class="section-signup__logo--' . $variant . '" src="'
            . htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" alt="'
            . htmlspecialchars($alt, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" height="40" /></a>';
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function formHtml(array $content, string $variant): string
    {
        $emailPlaceholder = htmlspecialchars(
            (string) ($content['email_placeholder'] ?? 'Email'),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8',
        );
        $passwordPlaceholder = htmlspecialchars(
            (string) ($content['password_placeholder'] ?? 'Mot de passe'),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8',
        );
        $confirmPlaceholder = htmlspecialchars(
            (string) ($content['confirm_placeholder'] ?? 'Confirmer le mot de passe'),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8',
        );
        $submit = htmlspecialchars(
            (string) ($content['button_text'] ?? 'Créer mon compte'),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8',
        );
        $emailLabel = htmlspecialchars(
            (string) ($content['email_label'] ?? 'Email'),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8',
        );
        $passwordLabel = htmlspecialchars(
            (string) ($content['password_label'] ?? 'Mot de passe'),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8',
        );
        $confirmLabel = htmlspecialchars(
            (string) ($content['confirm_label'] ?? 'Confirmer le mot de passe'),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8',
        );

        $emailField = $variant === 'signup2'
            ? self::labeledField($variant, 'email', 'email', $emailLabel, $emailPlaceholder, 'email', 'new-password')
            : self::plainField($variant, 'email', 'email', $emailLabel, $emailPlaceholder, 'email', 'new-password');

        $passwordField = $variant === 'signup2'
            ? self::labeledField($variant, 'password', 'password', $passwordLabel, $passwordPlaceholder, 'new-password', 'new-password')
            : self::plainField($variant, 'password', 'password', $passwordLabel, $passwordPlaceholder, 'new-password', 'new-password');

        $confirmField = $variant === 'signup2'
            ? self::labeledField($variant, 'confirm', 'password', $confirmLabel, $confirmPlaceholder, 'new-password', 'new-password')
            : self::plainField($variant, 'confirm', 'password', $confirmLabel, $confirmPlaceholder, 'new-password', 'new-password');

        return '<form class="section-signup__form--' . $variant . '" method="post" action="#" novalidate'
            . ' toolname="site_signup"'
            . ' tooldescription="Formulaire de création de compte."'
            . ' data-signup-form>'
            . '<div class="section-signup__card--' . $variant . '">'
            . $emailField
            . $passwordField
            . $confirmField
            . '<button type="submit" class="section-signup__submit--' . $variant . '">' . $submit . '</button>'
            . '</div></form>';
    }

    private static function plainField(
        string $variant,
        string $name,
        string $type,
        string $label,
        string $placeholder,
        string $autocomplete,
        string $passwordAutocomplete,
    ): string {
        $auto = $type === 'password' ? $passwordAutocomplete : $autocomplete;

        return '<input class="section-signup__input--' . $variant . '" id="signup-' . $name . '-' . $variant . '" name="' . $name . '" type="' . $type . '"'
            . ' placeholder="' . $placeholder . '" required autocomplete="' . $auto . '" aria-label="' . $label . '" />';
    }

    private static function labeledField(
        string $variant,
        string $name,
        string $type,
        string $label,
        string $placeholder,
        string $autocomplete,
        string $passwordAutocomplete,
    ): string {
        $auto = $type === 'password' ? $passwordAutocomplete : $autocomplete;

        return '<div class="section-signup__field--' . $variant . '">'
            . '<label class="section-signup__label--' . $variant . '" for="signup-' . $name . '-' . $variant . '">' . $label . '</label>'
            . '<input class="section-signup__input--' . $variant . '" id="signup-' . $name . '-' . $variant . '" name="' . $name . '" type="' . $type . '"'
            . ' placeholder="' . $placeholder . '" required autocomplete="' . $auto . '" />'
            . '</div>';
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function loginSwitchHtml(array $content, string $variant): string
    {
        $text = trim((string) ($content['login_text'] ?? ''));
        $linkLabel = trim((string) ($content['login_link_label'] ?? 'Se connecter'));
        if ($text === '' && $linkLabel === '') {
            return '';
        }

        $html = '<div class="section-signup__login-switch--' . $variant . '">';
        if ($text !== '') {
            $html .= '<p class="section-signup__login-text--' . $variant . '">'
                . htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
        }
        if ($linkLabel !== '') {
            $html .= '<button type="button" class="section-signup__login-link--' . $variant . '" data-auth-switch="login">'
                . htmlspecialchars($linkLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</button>';
        }
        $html .= '</div>';

        return $html;
    }
}
