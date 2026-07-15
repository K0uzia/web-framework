<?php

declare(strict_types=1);

namespace Capsule;

use App\Http\Dev\Sections\SectionDefaults;
use Capsule\Section\Login\LoginStyle;
use Capsule\Section\Signup\SignupStyle;

/**
 * Blocs de connexion stockés au niveau du site (hors pages éditoriales).
 */
final class LoginBlockLibrary
{
    public const REF_PREFIX = 'login:';

    /**
     * @param array<string, mixed> $site
     *
     * @return list<array<string, mixed>>
     */
    public static function blocks(array $site): array
    {
        $raw = is_array($site['login_blocks'] ?? null) ? $site['login_blocks'] : [];
        $blocks = [];
        foreach ($raw as $block) {
            if (is_array($block)) {
                $blocks[] = self::normalizeBlock($block);
            }
        }

        return $blocks !== [] ? $blocks : self::defaultBlocks();
    }

    /**
     * @param array<string, mixed> $site
     *
     * @return array<string, mixed>
     */
    public static function materialize(array $site): array
    {
        if (!is_array($site['login_blocks'] ?? null) || $site['login_blocks'] === []) {
            $site['login_blocks'] = self::defaultBlocks();
        } else {
            $site['login_blocks'] = array_map(
                static fn (array $block): array => self::normalizeBlock($block),
                array_values(array_filter($site['login_blocks'], is_array(...)),
            ));
        }

        return $site;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function defaultBlocks(): array
    {
        return [
            self::normalizeBlock([
                'id' => 'login1-default',
                'name' => 'Connexion compacte',
                'variant' => 'login1',
                'content' => SectionDefaults::content('login', 'login1'),
                'style' => LoginStyle::defaults('login1'),
            ]),
            self::normalizeBlock([
                'id' => 'login2-default',
                'name' => 'Connexion avec labels',
                'variant' => 'login2',
                'content' => SectionDefaults::content('login', 'login2'),
                'style' => LoginStyle::defaults('login2'),
            ]),
        ];
    }

    public static function ref(string $blockId): string
    {
        return self::REF_PREFIX . trim($blockId);
    }

    public static function pairedSignupVariant(string $loginVariant): string
    {
        return match (LoginStyle::normalizeVariant($loginVariant)) {
            'login2' => 'signup2',
            default => 'signup1',
        };
    }

    /**
     * @param array<string, mixed> $loginBlock
     *
     * @return list<array{type: string, variant: string}>
     */
    public static function authSectionRefs(array $loginBlock): array
    {
        $variant = LoginStyle::normalizeVariant((string) ($loginBlock['variant'] ?? 'login1'));

        return [
            ['type' => 'login', 'variant' => $variant],
            ['type' => 'signup', 'variant' => self::pairedSignupVariant($variant)],
        ];
    }

    /**
     * @param array<string, mixed>      $block
     * @param array<string, mixed>|null $siteInfo
     *
     * @return array<string, mixed>
     */
    public static function toSignupSection(array $block, ?array $siteInfo = null): array
    {
        $content = is_array($block['content'] ?? null) ? $block['content'] : [];
        if ($siteInfo !== null) {
            if (trim((string) ($content['logo_url'] ?? '')) === '') {
                $content['logo_url'] = trim((string) ($siteInfo['logo_url'] ?? ''));
            }
            if (trim((string) ($content['logo_alt'] ?? '')) === '') {
                $content['logo_alt'] = trim((string) ($siteInfo['name'] ?? ''));
            }
        }

        $variant = SignupStyle::normalizeVariant((string) ($block['variant'] ?? 'signup1'));

        return [
            'id' => (string) ($block['id'] ?? 'signup'),
            'type' => 'signup',
            'variant' => $variant,
            'visible' => true,
            'content' => $content,
            'style' => is_array($block['style'] ?? null) ? $block['style'] : SignupStyle::defaults($variant),
        ];
    }

    public static function blockIdFromRef(string $ref): string
    {
        $ref = trim($ref);
        if (!str_starts_with($ref, self::REF_PREFIX)) {
            return '';
        }

        return trim(substr($ref, strlen(self::REF_PREFIX)));
    }

    /**
     * @param array<string, mixed> $site
     *
     * @return array<string, mixed>|null
     */
    public static function find(array $site, string $ref): ?array
    {
        $id = self::blockIdFromRef($ref);
        if ($id === '') {
            return null;
        }
        foreach (self::blocks($site) as $block) {
            if (($block['id'] ?? '') === $id) {
                return $block;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed>      $block
     * @param array<string, mixed>|null $siteInfo
     *
     * @return array<string, mixed>
     */
    public static function toSection(array $block, ?array $siteInfo = null): array
    {
        $content = is_array($block['content'] ?? null) ? $block['content'] : [];
        if ($siteInfo !== null) {
            if (trim((string) ($content['logo_url'] ?? '')) === '') {
                $content['logo_url'] = trim((string) ($siteInfo['logo_url'] ?? ''));
            }
            if (trim((string) ($content['logo_alt'] ?? '')) === '') {
                $content['logo_alt'] = trim((string) ($siteInfo['name'] ?? ''));
            }
        }

        return [
            'id' => (string) ($block['id'] ?? 'login'),
            'type' => 'login',
            'variant' => LoginStyle::normalizeVariant((string) ($block['variant'] ?? 'login1')),
            'visible' => true,
            'content' => $content,
            'style' => is_array($block['style'] ?? null) ? $block['style'] : LoginStyle::defaults('login1'),
        ];
    }

    /**
     * @param array<string, mixed> $block
     *
     * @return array<string, mixed>
     */
    private static function normalizeBlock(array $block): array
    {
        $variant = LoginStyle::normalizeVariant((string) ($block['variant'] ?? 'login1'));
        $id = trim((string) ($block['id'] ?? ''));
        if ($id === '') {
            $id = 'login-' . substr(bin2hex(random_bytes(3)), 0, 6);
        }

        return [
            'id' => $id,
            'name' => trim((string) ($block['name'] ?? '')) !== '' ? trim((string) $block['name']) : 'Connexion',
            'variant' => $variant,
            'content' => is_array($block['content'] ?? null) ? $block['content'] : SectionDefaults::content('login', $variant),
            'style' => is_array($block['style'] ?? null) ? $block['style'] : LoginStyle::defaults($variant),
        ];
    }
}
