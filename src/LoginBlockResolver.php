<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Résout les blocs de connexion référencés par l'en-tête.
 */
final class LoginBlockResolver
{
    /**
     * @param array<string, mixed> $site
     *
     * @return array{source: string, section: array<string, mixed>}|null
     */
    public static function resolve(array $site, PageRepository $pages, string $ref, bool $publishedOnly = true): ?array
    {
        $ref = trim($ref);
        if ($ref === '') {
            return null;
        }

        if (str_starts_with($ref, LoginBlockLibrary::REF_PREFIX)) {
            $block = LoginBlockLibrary::find($site, $ref);
            if ($block === null) {
                return null;
            }

            return [
                'source' => 'site',
                'section' => LoginBlockLibrary::toSection($block, $site),
            ];
        }

        $sectionId = self::sectionIdFromRef($ref);
        if ($sectionId === '') {
            return null;
        }

        $pagePath = self::pagePathFromRef($ref);
        foreach ($pages->all($publishedOnly) as $page) {
            if ($page->routePath() !== $pagePath) {
                continue;
            }
            foreach ($page->sections as $section) {
                if (!is_array($section)) {
                    continue;
                }
                if (($section['id'] ?? '') !== $sectionId) {
                    continue;
                }
                $type = is_string($section['type'] ?? null) ? $section['type'] : '';
                if (!in_array($type, ['signup', 'waitlist'], true)) {
                    continue;
                }
                if (($section['visible'] ?? true) === false) {
                    continue;
                }

                return ['source' => 'page', 'section' => $section];
            }
        }

        return null;
    }

    public static function pagePathFromRef(string $ref): string
    {
        $ref = trim($ref);
        if ($ref === '') {
            return '';
        }
        if (str_starts_with($ref, LoginBlockLibrary::REF_PREFIX)) {
            return '/login';
        }
        if (str_starts_with($ref, '#')) {
            return '/';
        }
        $hashPos = strpos($ref, '#');
        if ($hashPos === false) {
            return $ref;
        }

        return substr($ref, 0, $hashPos) !== '' ? substr($ref, 0, $hashPos) : '/';
    }

    public static function sectionIdFromRef(string $ref): string
    {
        $ref = trim($ref);
        if ($ref === '' || !str_contains($ref, '#')) {
            return '';
        }

        return trim(substr($ref, (int) strpos($ref, '#') + 1));
    }

    /**
     * @param array<string, mixed> $site
     */
    public static function buildSelectOptions(array $site, string $current = ''): string
    {
        $options = ['<option value="">Aucun bloc sélectionné</option>'];
        $current = trim($current);

        foreach (LoginBlockLibrary::blocks($site) as $block) {
            $id = (string) ($block['id'] ?? '');
            if ($id === '') {
                continue;
            }
            $name = trim((string) ($block['name'] ?? ''));
            $variant = (string) ($block['variant'] ?? 'login1');
            $label = $name !== '' ? $name : 'Connexion';
            $value = LoginBlockLibrary::ref($id);
            $selected = $value === $current ? ' selected' : '';
            $options[] = '<option value="' . htmlspecialchars($value, ENT_QUOTES) . '"' . $selected . '>'
                . htmlspecialchars($label . ' (' . $variant . ')', ENT_QUOTES) . '</option>';
        }

        return implode('', $options);
    }

    /**
     * @param array<string, mixed> $login
     * @param array<string, mixed> $site
     */
    public static function effectiveHref(array $login, array $site, string $defaultHref = '/login'): string
    {
        $blockRef = trim((string) ($login['block_ref'] ?? ''));
        if ($blockRef !== '' && (string) ($login['display'] ?? 'page') === 'page') {
            $path = self::pagePathFromRef($blockRef);

            return $path !== '' ? $path : $defaultHref;
        }

        $href = trim((string) ($login['href'] ?? ''));

        return $href !== '' ? $href : $defaultHref;
    }

    /**
     * @param array<string, mixed> $site
     *
     * @return array<string, mixed>|null
     */
    public static function resolveActiveHeaderBlock(array $site): ?array
    {
        $header = ChromeVariants::resolveHeader($site);
        $login = is_array($header['login'] ?? null) ? $header['login'] : [];
        if (($login['enabled'] ?? false) !== true) {
            return null;
        }

        $ref = trim((string) ($login['block_ref'] ?? ''));
        if ($ref === '') {
            $blocks = LoginBlockLibrary::blocks($site);

            return $blocks[0] ?? null;
        }

        return LoginBlockLibrary::find($site, $ref);
    }
}
