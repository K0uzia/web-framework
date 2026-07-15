<?php

declare(strict_types=1);

namespace Capsule;

use App\Http\Dev\Sections\SectionDefaults;
use Capsule\Section\Login\LoginVariantRenderer;
use Capsule\Section\Signup\SignupStyle;

/**
 * Compose les panneaux login et inscription appairés (login1/signup1, login2/signup2).
 */
final class AuthBlockRenderer
{
    public function __construct(
        private readonly SectionRenderer $sections,
    ) {
    }

    /**
     * @param array<string, mixed> $loginBlock
     * @param array<string, mixed> $site
     */
    public function renderPair(array $loginBlock, array $site): string
    {
        $loginSection = LoginBlockLibrary::toSection($loginBlock, $site);
        $signupVariant = LoginBlockLibrary::pairedSignupVariant((string) ($loginBlock['variant'] ?? 'login1'));
        $signupSection = LoginBlockLibrary::toSignupSection([
            'id' => (string) ($loginBlock['id'] ?? 'signup') . '-signup',
            'variant' => $signupVariant,
            'content' => SectionDefaults::content('signup', $signupVariant),
            'style' => SignupStyle::defaults($signupVariant),
        ], $site);

        $loginHtml = $this->sections->renderOne($loginSection);
        $signupHtml = $this->sections->renderOne($signupSection);
        $forgotHtml = LoginVariantRenderer::forgotPanelHtml(
            is_array($loginSection['content'] ?? null) ? $loginSection['content'] : [],
            (string) ($loginSection['variant'] ?? 'login1'),
        );
        if ($loginHtml === '' && $signupHtml === '' && $forgotHtml === '') {
            return '';
        }

        return '<div class="site-auth" data-auth-root data-auth-mode="login">'
            . '<div class="site-auth__panel" data-auth-panel="login">' . $loginHtml . '</div>'
            . '<div class="site-auth__panel" data-auth-panel="signup" hidden>' . $signupHtml . '</div>'
            . '<div class="site-auth__panel" data-auth-panel="forgot" hidden>' . $forgotHtml . '</div>'
            . '</div>';
    }

    /**
     * @param array<string, mixed> $loginBlock
     * @param array<string, mixed> $site
     *
     * @return list<array{type: string, variant: string}>
     */
    public function sectionRefs(array $loginBlock, array $site): array
    {
        return LoginBlockLibrary::authSectionRefs($loginBlock);
    }

    /**
     * @param array<string, mixed> $loginBlock
     * @param array<string, mixed> $site
     */
    public function forgotPanelHtml(array $loginBlock, array $site): string
    {
        $loginSection = LoginBlockLibrary::toSection($loginBlock, $site);
        $content = is_array($loginSection['content'] ?? null) ? $loginSection['content'] : [];
        $variant = (string) ($loginSection['variant'] ?? 'login1');

        return LoginVariantRenderer::forgotPanelHtml($content, $variant);
    }
}
