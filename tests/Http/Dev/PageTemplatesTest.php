<?php

declare(strict_types=1);

namespace Tests\Http\Dev;

use App\Http\Dev\PageTemplates;
use PHPUnit\Framework\TestCase;

final class PageTemplatesTest extends TestCase
{
    public function testHasFortyNinePlusTemplates(): void
    {
        $this->assertGreaterThanOrEqual(49, count(PageTemplates::definitions()));
    }

    public function testAboutTemplateSectionsArePopulated(): void
    {
        $sections = PageTemplates::sections('about-1');
        $this->assertGreaterThanOrEqual(4, count($sections));
        $this->assertSame('hero', $sections[0]['type']);
    }

    public function testAboutPresetSlugIsAbout(): void
    {
        $this->assertSame('about', PageTemplates::resolveSlug('about-1', true));
        $this->assertTrue(PageTemplates::publishByDefault('about-1'));
        $this->assertFalse(PageTemplates::publishByDefault('blank'));
    }

    public function testLandingUsesHomeWhenAvailable(): void
    {
        $this->assertSame('', PageTemplates::resolveSlug('landing-01', false));
        $this->assertSame('accueil-alt', PageTemplates::resolveSlug('landing-01', true));
    }

    public function testPresetsJsonIsValid(): void
    {
        $data = json_decode(PageTemplates::presetsJson(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('blog-1', $data);
        $this->assertSame('blog', $data['blog-1']['slug']);
    }
}
