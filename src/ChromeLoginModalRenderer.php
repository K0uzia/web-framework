<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Rendu de la modale de connexion injectée dans le layout public.
 */
final class ChromeLoginModalRenderer
{
    public function __construct(
        private readonly PageRepository $pages,
        private readonly AuthBlockRenderer $authBlocks,
    ) {
    }

    /**
     * @param array<string, mixed> $login
     * @param array<string, mixed> $site
     *
     * @return array<string, mixed>|null
     */
    public function resolveLoginBlock(array $login, array $site, bool $publishedOnly = true): ?array
    {
        if (($login['enabled'] ?? false) !== true) {
            return null;
        }
        if ((string) ($login['display'] ?? 'page') !== 'modal') {
            return null;
        }

        $blockRef = trim((string) ($login['block_ref'] ?? ''));
        if ($blockRef === '') {
            return LoginBlockResolver::resolveActiveHeaderBlock($site);
        }

        if (str_starts_with($blockRef, LoginBlockLibrary::REF_PREFIX)) {
            return LoginBlockLibrary::find($site, $blockRef);
        }

        $resolved = LoginBlockResolver::resolve($site, $this->pages, $blockRef, $publishedOnly);
        if ($resolved === null) {
            return null;
        }
        $section = $resolved['section'] ?? null;
        if (!is_array($section) || ($section['type'] ?? '') !== 'login') {
            return null;
        }

        return [
            'id' => (string) ($section['id'] ?? 'login-page'),
            'name' => 'Connexion',
            'variant' => (string) ($section['variant'] ?? 'login1'),
            'content' => is_array($section['content'] ?? null) ? $section['content'] : [],
            'style' => is_array($section['style'] ?? null) ? $section['style'] : [],
        ];
    }

    /**
     * @param array<string, mixed> $loginBlock
     * @param array<string, mixed> $site
     */
    public function renderHtml(array $loginBlock, array $site): string
    {
        $authHtml = $this->authBlocks->renderPair($loginBlock, $site);
        if ($authHtml === '') {
            return '';
        }

        return '<div class="site-login-modal" id="site-login-modal" role="dialog" aria-modal="true"'
            . ' aria-labelledby="site-login-modal-title" hidden>'
            . '<div class="site-login-modal__backdrop" data-login-modal-close tabindex="-1" aria-hidden="true"></div>'
            . '<div class="site-login-modal__panel" role="document">'
            . '<button type="button" class="site-login-modal__close" data-login-modal-close'
            . ' aria-label="Fermer la fenêtre de connexion">'
            . '<i class="fa-solid fa-xmark" aria-hidden="true"></i></button>'
            . '<div class="site-login-modal__content" id="site-login-modal-title">' . $authHtml . '</div>'
            . '</div></div>';
    }

    /**
     * @param array<string, mixed> $login
     * @param array<string, mixed> $site
     */
    public function render(array $login, array $site, bool $publishedOnly = true): string
    {
        $block = $this->resolveLoginBlock($login, $site, $publishedOnly);
        if ($block === null) {
            return '';
        }

        return $this->renderHtml($block, $site);
    }
}
