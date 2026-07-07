<?php

declare(strict_types=1);

namespace Tests;

use App\Http\Dev\SectionDefaults;
use Capsule\StockImages;
use PHPUnit\Framework\TestCase;

final class StockImagesTest extends TestCase
{
    public function testPathUsesLocalAssetsStock(): void
    {
        $url = StockImages::hero(0);

        $this->assertStringStartsWith('/assets/stock/', $url);
        $this->assertStringEndsWith('.jpg', $url);
        $this->assertStringNotContainsString('unsplash', $url);
    }

    public function testSectionDefaultsProvideLocalImagesForVisualBlocks(): void
    {
        $hero = SectionDefaults::content('hero', 'split');
        $gallery = SectionDefaults::content('gallery');
        $projects = SectionDefaults::content('projects');
        $embed = SectionDefaults::content('ui-embed');

        $this->assertStringStartsWith('/assets/stock/', (string) ($hero['image_url'] ?? ''));
        $this->assertStringStartsWith('/assets/stock/', (string) ($gallery['items'][0]['url'] ?? ''));
        $this->assertStringStartsWith('/assets/stock/', (string) ($projects['items'][1]['url'] ?? ''));
        $this->assertStringStartsWith('/assets/stock/', (string) ($embed['image_url'] ?? ''));
    }

    public function testCenteredHeroHasNoDefaultImage(): void
    {
        $hero = SectionDefaults::content('hero', 'centered');

        $this->assertSame('', $hero['image_url'] ?? null);
    }

    public function testResolveReplacesUnsplashUrlWithLocalFallback(): void
    {
        $url = StockImages::resolve(
            'https://images.unsplash.com/photo-123?w=1200',
            static fn (): string => StockImages::hero(1),
        );

        $this->assertStringStartsWith('/assets/stock/', $url);
    }

    public function testAllListsEightUniqueFiles(): void
    {
        $all = StockImages::all();

        $this->assertCount(8, $all);
        $this->assertSame($all, array_unique($all));
    }
}
