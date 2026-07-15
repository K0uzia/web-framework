<?php

declare(strict_types=1);

namespace Tests;

use Capsule\HeroStyle;
use PHPUnit\Framework\TestCase;

final class HeroStyleTest extends TestCase
{
    public function testHero3Defaults(): void
    {
        $defaults = HeroStyle::defaults('hero3');

        $this->assertSame('background', $defaults['bg']);
        $this->assertSame('xl', $defaults['padding']);
    }

    public function testModifierClassesEmptyForHero3(): void
    {
        $this->assertSame('', HeroStyle::modifierClasses(['padding' => 'xl'], 'hero3'));
    }

    public function testRenderHero3Reviews(): void
    {
        $html = HeroStyle::renderHero3Reviews([
            'reviews_rating' => '5.0',
            'reviews_count' => '200',
            'review_avatars' => [
                ['url' => '/uploads/media/avatar.webp', 'title' => 'Test'],
            ],
        ]);

        $this->assertStringContainsString('section-hero__reviews', $html);
        $this->assertStringContainsString('fa-star', $html);
        $this->assertStringContainsString('200', $html);
    }

    public function testRenderHero3VisualWithDarkImage(): void
    {
        $html = HeroStyle::renderHero3Visual([
            'title' => 'Demo',
            'image_url' => '/uploads/media/hero-light.webp',
            'image_url_dark' => '/uploads/media/hero-dark.webp',
        ]);

        $this->assertStringContainsString('section-hero__img--light', $html);
        $this->assertStringContainsString('section-hero__img--dark', $html);
    }

    public function testVideoEmbedFromYoutubeUrl(): void
    {
        $html = HeroStyle::videoEmbedHtml('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

        $this->assertStringContainsString('section-hero__iframe', $html);
        $this->assertStringContainsString('youtube-nocookie.com/embed/', $html);
        $this->assertStringContainsString('referrerpolicy="strict-origin-when-cross-origin"', $html);
        $this->assertStringNotContainsString('web-share', $html);
    }

    public function testVideoBackgroundFromYoutubeUrl(): void
    {
        $html = HeroStyle::videoBackgroundHtml('https://youtu.be/dQw4w9WgXcQ');

        $this->assertStringContainsString('section-hero__backdrop-iframe', $html);
        $this->assertStringContainsString('autoplay=1', $html);
        $this->assertStringContainsString('youtube-nocookie.com/embed/', $html);
    }

    public function testRenderBackdropForFullscreenVideo(): void
    {
        $html = HeroStyle::renderBackdrop([
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'video_controls' => 'off',
        ], 'fullscreen');

        $this->assertStringContainsString('section-hero__backdrop', $html);
        $this->assertStringContainsString('section-hero__backdrop--chromeless', $html);
        $this->assertStringContainsString('section-hero__backdrop-overlay', $html);
        $this->assertStringContainsString('section-hero__backdrop-iframe', $html);
        $this->assertStringContainsString('section-hero__video-frame-zoom', $html);
    }

    public function testRenderBackdropIgnoredForCentered(): void
    {
        $this->assertSame('', HeroStyle::renderBackdrop([
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ], 'centered'));
    }

    public function testVideoEmbedHidesControlsWhenDisabled(): void
    {
        $html = HeroStyle::videoEmbedHtml('https://www.youtube.com/watch?v=dQw4w9WgXcQ', [
            'video_controls' => 'off',
            'video_autoplay' => 'off',
        ]);

        $this->assertStringContainsString('controls=0', $html);
        $this->assertStringContainsString('enablejsapi=1', $html);
        $this->assertStringContainsString('autoplay=1', $html);
        $this->assertStringContainsString('data-src=', $html);
        $this->assertStringNotContainsString(' src=', $html);
        $this->assertStringContainsString('data-hero-video-chromeless="1"', $html);
        $this->assertStringContainsString('section-hero__video-chrome-mask', $html);
        $this->assertStringContainsString('section-hero__video-frame-zoom', $html);
        $this->assertStringNotContainsString('allowfullscreen', $html);
    }

    public function testRenderHeroShaderBackdrop(): void
    {
        $html = \Capsule\Section\Hero\HeroStyle::renderHeroBackdrop([
            'background_type' => 'shader',
            'background_shader_id' => 'shader3-sky',
            'background_shader_color' => '#7dd3fc',
        ]);

        $this->assertStringContainsString('data-hero-shader', $html);
        $this->assertStringContainsString('shader3-sky', $html);
        $this->assertStringContainsString('section-hero__shader-canvas', $html);
        $this->assertStringContainsString('--shader-fallback:#0f172a', $html);
        $this->assertStringContainsString('data-shader-color="#7dd3fc"', $html);
    }

    public function testRenderHeroVideoBackdropFromBackgroundField(): void
    {
        $html = \Capsule\Section\Hero\HeroStyle::renderHeroBackdrop([
            'background_type' => 'video',
            'background_video_url' => 'https://youtu.be/dQw4w9WgXcQ',
            'video_controls' => 'off',
        ]);

        $this->assertStringContainsString('section-hero__backdrop--video', $html);
        $this->assertStringContainsString('section-hero__backdrop-iframe', $html);
    }

    public function testBadgeFieldOnlyForBadgeVariant(): void
    {
        $field = ['show_for_variants' => ['badge']];

        $this->assertTrue(HeroStyle::fieldAppliesToVariant($field, 'badge'));
        $this->assertFalse(HeroStyle::fieldAppliesToVariant($field, 'centered'));
    }

    public function testLocalVideoBackdropIncludesBaseClassAndFit(): void
    {
        $html = HeroStyle::videoBackgroundHtml('/uploads/media/imports/vid-test/video.mp4', [
            'video_fit' => 'contain',
            'video_controls' => 'off',
        ]);

        $this->assertStringContainsString('class="section-hero__backdrop-video section-hero__backdrop-video--fit-contain"', $html);
    }

    public function testLocalVideoEmbedIncludesBaseClassAndFit(): void
    {
        $html = HeroStyle::videoEmbedHtml('/uploads/media/imports/vid-test/video.mp4', [
            'video_fit' => 'cover',
        ]);

        $this->assertStringContainsString('class="section-hero__video-file section-hero__video-file--fit-cover"', $html);
        $this->assertStringContainsString('section-hero__video--file', $html);
    }

    public function testRenderBackdropForLocalVideo(): void
    {
        $html = HeroStyle::renderBackdrop([
            'video_url' => '/uploads/media/imports/vid-test/video.mp4',
            'video_fit' => 'fill',
            'video_controls' => 'off',
        ], 'fullscreen');

        $this->assertStringContainsString('section-hero__backdrop-video section-hero__backdrop-video--fit-fill', $html);
        $this->assertStringNotContainsString('section-hero__video-frame-zoom', $html);
    }
}
