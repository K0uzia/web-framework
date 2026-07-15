<?php

declare(strict_types=1);

namespace Tests;

use Capsule\LoginBlockLibrary;
use Capsule\LoginBlockResolver;
use Capsule\Page;
use Capsule\PageRepository;
use PHPUnit\Framework\TestCase;

final class LoginBlockResolverTest extends TestCase
{
    public function testPagePathFromRef(): void
    {
        $this->assertSame('/login', LoginBlockResolver::pagePathFromRef('login:login1-default'));
        $this->assertSame('/', LoginBlockResolver::pagePathFromRef('#login-abc'));
        $this->assertSame('/connexion', LoginBlockResolver::pagePathFromRef('/connexion#login-abc'));
    }

    public function testResolveFindsSiteLoginBlock(): void
    {
        $site = LoginBlockLibrary::materialize([]);
        $resolved = LoginBlockResolver::resolve($site, new PageRepository(new \PDO('sqlite::memory:')), 'login:login1-default');
        $this->assertNotNull($resolved);
        $this->assertSame('site', $resolved['source']);
        $this->assertSame('login', $resolved['section']['type']);
        $this->assertSame('login1', $resolved['section']['variant']);
    }

    public function testBuildSelectOptionsListsSiteBlocks(): void
    {
        $site = LoginBlockLibrary::materialize([]);
        $html = LoginBlockResolver::buildSelectOptions($site);
        $this->assertStringContainsString('login:login1-default', $html);
        $this->assertStringContainsString('login:login2-default', $html);
    }

    public function testEffectiveHrefUsesLoginPageForSiteBlock(): void
    {
        $href = LoginBlockResolver::effectiveHref([
            'enabled' => true,
            'display' => 'page',
            'block_ref' => 'login:login2-default',
            'href' => '/login',
        ], []);

        $this->assertSame('/login', $href);
    }
}
