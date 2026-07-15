<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Rendu des boutons du chrome (CTA, connexion) avec styles configurables.
 */
final class ChromeButtonRenderer
{
  /** @var array<string, string> */
    public const STYLES = [
        'primary' => 'Plein (primaire)',
        'outline' => 'Contour',
        'ghost' => 'Léger',
        'link' => 'Lien texte',
    ];

    /**
     * @param array<string, mixed> $button
     * @param array<string, mixed> $site
     */
    public static function render(
        array $button,
        string $defaultStyle = 'outline',
        string $defaultHref = '/login',
        array $site = [],
    ): string {
        if (($button['enabled'] ?? false) !== true) {
            return '';
        }

        $label = trim((string) ($button['label'] ?? ''));
        if ($label === '') {
            $label = 'Connexion';
        }

        $style = (string) ($button['style'] ?? $defaultStyle);
        if (!isset(self::STYLES[$style])) {
            $style = $defaultStyle;
        }

        $display = (string) ($button['display'] ?? 'page');
        if ($display === 'modal') {
            return '<button type="button" class="' . self::classFor($style)
                . '" data-login-modal-open aria-haspopup="dialog" aria-controls="site-login-modal">'
                . htmlspecialchars($label, ENT_QUOTES) . '</button>';
        }

        $href = LoginBlockResolver::effectiveHref($button, $site, $defaultHref);
        if ($href === '') {
            return '';
        }

        return '<a class="' . self::classFor($style) . '" href="'
            . htmlspecialchars($href, ENT_QUOTES) . '">'
            . htmlspecialchars($label, ENT_QUOTES) . '</a>';
    }

    public static function classFor(string $style): string
    {
        return 'site-chrome-btn site-chrome-btn--' . $style;
    }

    /**
     * @param array<string, string> $styles
     */
    public static function optionsHtml(string $current, array $styles = self::STYLES): string
    {
        $options = [];
        foreach ($styles as $value => $label) {
            $selected = $value === $current ? ' selected' : '';
            $options[] = '<option value="' . htmlspecialchars($value, ENT_QUOTES) . '"' . $selected . '>'
                . htmlspecialchars($label, ENT_QUOTES) . '</option>';
        }

        return implode('', $options);
    }
}
