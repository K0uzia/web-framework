<?php

declare(strict_types=1);

namespace Capsule\Section\Login;

/**
 * Rendu HTML des variantes login (shadcnblocks login1, login2).
 */
final class LoginVariantRenderer
{
    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    public static function enrich(array $data, array $content, string $variant): array
    {
        $variant = LoginStyle::normalizeVariant($variant);
        $data['login_logo_html'] = self::logoHtml($content, $variant);
        $data['login_form_html'] = self::formHtml($content, $variant);
        $data['login_signup_html'] = self::signupHtml($content, $variant);

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

        return '<a class="section-login__logo-link--' . $variant . '" href="'
            . htmlspecialchars($home !== '' ? $home : '/', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">'
            . '<img class="section-login__logo--' . $variant . '" src="'
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
        $submit = htmlspecialchars(
            (string) ($content['button_text'] ?? 'Se connecter'),
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

        $emailField = $variant === 'login2'
            ? '<div class="section-login__field--' . $variant . '">'
                . '<label class="section-login__label--' . $variant . '" for="login-email-' . $variant . '">' . $emailLabel . '</label>'
                . '<input class="section-login__input--' . $variant . '" id="login-email-' . $variant . '" name="email" type="email"'
                . ' placeholder="' . $emailPlaceholder . '" required autocomplete="email" />'
                . '</div>'
            : '<input class="section-login__input--' . $variant . '" id="login-email-' . $variant . '" name="email" type="email"'
                . ' placeholder="' . $emailPlaceholder . '" required autocomplete="email" aria-label="' . $emailLabel . '" />';

        $passwordField = $variant === 'login2'
            ? '<div class="section-login__field--' . $variant . '">'
                . '<label class="section-login__label--' . $variant . '" for="login-password-' . $variant . '">' . $passwordLabel . '</label>'
                . '<input class="section-login__input--' . $variant . '" id="login-password-' . $variant . '" name="password" type="password"'
                . ' placeholder="' . $passwordPlaceholder . '" required autocomplete="current-password" />'
                . '</div>'
            : '<input class="section-login__input--' . $variant . '" id="login-password-' . $variant . '" name="password" type="password"'
                . ' placeholder="' . $passwordPlaceholder . '" required autocomplete="current-password" aria-label="' . $passwordLabel . '" />';

        $forgotHtml = self::forgotPasswordHtml($content, $variant);

        return '<form class="section-login__form--' . $variant . '" method="post" action="#" novalidate'
            . ' toolname="site_login"'
            . ' tooldescription="Formulaire de connexion au site."'
            . ' data-login-form>'
            . '<div class="section-login__card--' . $variant . '">'
            . $emailField
            . $passwordField
            . $forgotHtml
            . '<button type="submit" class="section-login__submit--' . $variant . '">' . $submit . '</button>'
            . '</div></form>';
    }

    /**
     * @param array<string, mixed> $content
     */
    public static function forgotPanelHtml(array $content, string $variant): string
    {
        $variant = LoginStyle::normalizeVariant($variant);
        $heading = htmlspecialchars(
            (string) ($content['forgot_heading'] ?? 'Mot de passe oublié'),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8',
        );
        $description = htmlspecialchars(
            (string) ($content['forgot_description'] ?? 'Saisissez votre adresse email. Nous vous enverrons un lien de réinitialisation.'),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8',
        );
        $emailPlaceholder = htmlspecialchars(
            (string) ($content['email_placeholder'] ?? 'Email'),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8',
        );
        $emailLabel = htmlspecialchars(
            (string) ($content['email_label'] ?? 'Email'),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8',
        );
        $submit = htmlspecialchars(
            (string) ($content['forgot_button_text'] ?? 'Envoyer le lien'),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8',
        );
        $backLabel = htmlspecialchars(
            (string) ($content['forgot_back_label'] ?? 'Retour à la connexion'),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8',
        );

        $emailField = $variant === 'login2'
            ? '<div class="section-login__field--' . $variant . '">'
                . '<label class="section-login__label--' . $variant . '" for="forgot-email-' . $variant . '">' . $emailLabel . '</label>'
                . '<input class="section-login__input--' . $variant . '" id="forgot-email-' . $variant . '" name="email" type="email"'
                . ' placeholder="' . $emailPlaceholder . '" required autocomplete="email" />'
                . '</div>'
            : '<input class="section-login__input--' . $variant . '" id="forgot-email-' . $variant . '" name="email" type="email"'
                . ' placeholder="' . $emailPlaceholder . '" required autocomplete="email" aria-label="' . $emailLabel . '" />';

        return '<div class="section-login__forgot-panel--' . $variant . '">'
            . '<div class="section-login__forgot-intro--' . $variant . '">'
            . '<h2 class="section-login__forgot-heading--' . $variant . '">' . $heading . '</h2>'
            . '<p class="section-login__forgot-text--' . $variant . '">' . $description . '</p>'
            . '</div>'
            . '<form class="section-login__forgot-form--' . $variant . '" method="post" action="#" novalidate'
            . ' toolname="site_forgot_password"'
            . ' tooldescription="Demande de réinitialisation du mot de passe."'
            . ' data-forgot-form>'
            . '<div class="section-login__card--' . $variant . '">'
            . $emailField
            . '<button type="submit" class="section-login__submit--' . $variant . '">' . $submit . '</button>'
            . '</div></form>'
            . '<button type="button" class="section-login__forgot-back--' . $variant . '" data-auth-switch="login">'
            . $backLabel . '</button>'
            . '</div>';
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function forgotPasswordHtml(array $content, string $variant): string
    {
        $label = trim((string) ($content['forgot_password_label'] ?? 'Mot de passe oublié ?'));
        $url = trim((string) ($content['forgot_password_url'] ?? ''));
        if ($label === '') {
            return '';
        }

        if ($url === '' || $url === '#') {
            return '<div class="section-login__forgot--' . $variant . '">'
                . '<button type="button" class="section-login__forgot-link--' . $variant . '" data-auth-switch="forgot">'
                . htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</button>'
                . '</div>';
        }

        return '<div class="section-login__forgot--' . $variant . '">'
            . '<a class="section-login__forgot-link--' . $variant . '" href="'
            . htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">'
            . htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</a>'
            . '</div>';
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function signupHtml(array $content, string $variant): string
    {
        $text = trim((string) ($content['signup_text'] ?? ''));
        $linkLabel = trim((string) ($content['signup_link_label'] ?? 'Créer un compte'));
        if ($text === '' && $linkLabel === '') {
            return '';
        }

        $html = '<div class="section-login__signup--' . $variant . '">';
        if ($text !== '') {
            $html .= '<p class="section-login__signup-text--' . $variant . '">'
                . htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
        }
        if ($linkLabel !== '') {
            $html .= '<button type="button" class="section-login__signup-link--' . $variant . '" data-auth-switch="signup">'
                . htmlspecialchars($linkLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</button>';
        }
        $html .= '</div>';

        return $html;
    }
}
