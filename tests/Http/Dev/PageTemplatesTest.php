<?php

declare(strict_types=1);

namespace Tests\Http\Dev;

use App\Http\Dev\PageTemplates;
use PHPUnit\Framework\TestCase;

final class PageTemplatesTest extends TestCase
{
    public function testHasStarterTemplates(): void
    {
        $defs = PageTemplates::definitions();
        $this->assertCount(2, $defs);
        $this->assertSame('blank', $defs[0]['id']);
        $this->assertSame('landing-hero3', $defs[1]['id']);
    }

    public function testLandingHero3SectionsArePopulated(): void
    {
        $sections = PageTemplates::sections('landing-hero3');
        $this->assertCount(1, $sections);
        $this->assertSame('hero', $sections[0]['type']);
        $this->assertSame('hero3', $sections[0]['variant']);
    }

    public function testLandingHero3UsesHomeSlug(): void
    {
        $this->assertSame('', PageTemplates::resolveSlug('landing-hero3', false));
        $this->assertSame('accueil-alt', PageTemplates::resolveSlug('landing-hero3', true));
        $this->assertTrue(PageTemplates::publishByDefault('landing-hero3'));
        $this->assertFalse(PageTemplates::publishByDefault('blank'));
    }

    public function testPresetsJsonIsValid(): void
    {
        $data = json_decode(PageTemplates::presetsJson(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('landing-hero3', $data);
        $this->assertSame('', $data['landing-hero3']['slug']);
    }
}
