<?php

declare(strict_types=1);

namespace Tests;

use Capsule\AuthBlockRenderer;
use Capsule\LoginBlockLibrary;
use Capsule\SectionRenderer;
use Capsule\Section\SectionHandlerRegistry;
use Capsule\View;
use PHPUnit\Framework\TestCase;

final class AuthBlockRendererTest extends TestCase
{
    public function testRenderPairIncludesLoginSignupSwitchAndForgotPassword(): void
    {
        $site = LoginBlockLibrary::materialize([]);
        $block = LoginBlockLibrary::blocks($site)[0];
        $renderer = new SectionRenderer(
            new View(dirname(__DIR__) . '/resources/layouts', dirname(__DIR__) . '/resources/partials'),
            dirname(__DIR__) . '/resources/sections',
            false,
            new SectionHandlerRegistry(),
        );
        $auth = new AuthBlockRenderer($renderer);

        $html = $auth->renderPair($block, $site);

        $this->assertStringContainsString('data-auth-root', $html);
        $this->assertStringContainsString('data-auth-panel="login"', $html);
        $this->assertStringContainsString('data-auth-panel="signup"', $html);
        $this->assertStringContainsString('data-auth-switch="signup"', $html);
        $this->assertStringContainsString('data-auth-switch="login"', $html);
        $this->assertStringContainsString('data-auth-switch="forgot"', $html);
        $this->assertStringContainsString('data-auth-panel="forgot"', $html);
        $this->assertStringContainsString('Mot de passe oublié', $html);
        $this->assertStringContainsString('section-signup--signup1', $html);
    }

    public function testPairedSignupVariantMapsLogin2ToSignup2(): void
    {
        $this->assertSame('signup1', LoginBlockLibrary::pairedSignupVariant('login1'));
        $this->assertSame('signup2', LoginBlockLibrary::pairedSignupVariant('login2'));
    }

    public function testSectionRefsIncludeLoginAndSignup(): void
    {
        $site = LoginBlockLibrary::materialize([]);
        $block = LoginBlockLibrary::blocks($site)[1];
        $refs = (new AuthBlockRenderer(
            new SectionRenderer(
                new View(dirname(__DIR__) . '/resources/layouts', dirname(__DIR__) . '/resources/partials'),
                dirname(__DIR__) . '/resources/sections',
                false,
                new SectionHandlerRegistry(),
            ),
        ))->sectionRefs($block, $site);

        $this->assertSame([
            ['type' => 'login', 'variant' => 'login2'],
            ['type' => 'signup', 'variant' => 'signup2'],
        ], $refs);
    }
}
