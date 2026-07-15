<?php

declare(strict_types=1);

namespace Tests;

use Capsule\ChromeButtonRenderer;
use PHPUnit\Framework\TestCase;

final class ChromeButtonRendererTest extends TestCase
{
    public function testRenderUsesDefaultLabelWhenEnabled(): void
    {
        $html = ChromeButtonRenderer::render([
            'enabled' => true,
            'label' => '',
            'href' => '/login',
            'style' => 'outline',
        ]);

        $this->assertStringContainsString('Connexion', $html);
        $this->assertStringContainsString('href="/login"', $html);
    }

    public function testRenderModalUsesButtonTrigger(): void
    {
        $html = ChromeButtonRenderer::render([
            'enabled' => true,
            'label' => 'Se connecter',
            'href' => '/login',
            'style' => 'outline',
            'display' => 'modal',
        ]);

        $this->assertStringContainsString('data-login-modal-open', $html);
        $this->assertStringContainsString('<button', $html);
        $this->assertStringNotContainsString('href=', $html);
    }
}
