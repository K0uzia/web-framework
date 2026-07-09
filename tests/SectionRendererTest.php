<?php

declare(strict_types=1);

namespace Tests;

use App\Http\Dev\SectionDefaults;
use Capsule\SectionRenderer;
use Capsule\View;
use PHPUnit\Framework\TestCase;

final class SectionRendererTest extends TestCase
{
    private SectionRenderer $renderer;

    protected function setUp(): void
    {
        $root = dirname(__DIR__);
        $this->renderer = new SectionRenderer(
            new View($root . '/resources/layouts', $root . '/resources/partials'),
            $root . '/resources/sections',
            true,
        );
    }

    public function testRendersHero3Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'hero-1',
            'type' => 'hero',
            'variant' => 'hero3',
            'content' => [
                'title' => 'Hello',
                'subtitle' => 'World',
                'reviews_rating' => '4.9',
                'reviews_count' => '120',
                'review_avatars' => [
                    ['url' => '/uploads/media/avatar.webp', 'title' => 'Alice'],
                ],
                'buttons' => [
                    ['label' => 'Go', 'href' => '/go', 'style' => 'primary'],
                    ['label' => 'More', 'href' => '/more', 'style' => 'secondary'],
                ],
                'image_url' => '/uploads/media/hero.webp',
            ],
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-hero--hero3', $html);
        $this->assertStringContainsString('Hello', $html);
        $this->assertStringContainsString('World', $html);
        $this->assertStringContainsString('section-hero__reviews', $html);
        $this->assertStringContainsString('fa-arrow-right', $html);
        $this->assertStringContainsString('section-hero__img--light', $html);
    }

    public function testSkipsHiddenSection(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'hero-hidden',
            'type' => 'hero',
            'variant' => 'hero3',
            'visible' => false,
            'content' => [
                'title' => 'Hidden',
                'subtitle' => 'Nope',
            ],
            'style' => ['bg' => 'background', 'padding' => 'lg'],
        ]);

        $this->assertSame('', $html);
    }

    public function testExtractSectionRefs(): void
    {
        $refs = $this->renderer->extractSectionRefs([
            ['type' => 'hero', 'variant' => 'hero3'],
        ]);

        $this->assertSame([
            ['type' => 'hero', 'variant' => 'hero3'],
        ], $refs);
    }

    public function testRendersFeature3Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'features-1',
            'type' => 'features',
            'variant' => 'feature3',
            'content' => [
                'title' => 'Fonctionnalités',
                'items' => [
                    ['title' => 'Item 1', 'text' => 'Description 1', 'url' => '/img1.svg'],
                    ['title' => 'Item 2', 'text' => 'Description 2', 'url' => '/img2.svg'],
                ],
            ],
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-features--feature3', $html);
        $this->assertStringContainsString('Fonctionnalités', $html);
        $this->assertStringContainsString('Item 1', $html);
        $this->assertStringContainsString('section-features__card-header', $html);
    }

    public function testRendersHero2WithoutImage(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'hero-split',
            'type' => 'hero',
            'variant' => 'hero3',
            'content' => [
                'title' => 'Titre',
                'subtitle' => 'Sous-titre',
                'buttons' => [['label' => 'Go', 'href' => '#', 'style' => 'primary']],
            ],
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('Titre', $html);
        $this->assertStringNotContainsString('section-hero__img', $html);
        $this->assertStringNotContainsString('/assets/stock/', $html);
    }

    public function testRendersFeature239OverlayLink(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'features-239',
            'type' => 'features',
            'variant' => 'feature239',
            'content' => [
                'title' => 'Titre',
                'subtitle' => 'Description',
                'image_url' => '/assets/sections/features/_shared/images/1-1x1.jpg',
                'overlay_date' => '2025 | Mars',
                'overlay_title' => 'Collection',
                'overlay_text' => 'Texte overlay',
                'overlay_link' => '#collection',
                'overlay_link_label' => 'Tout voir',
                'buttons' => [['label' => 'Parcourir', 'href' => '#', 'style' => 'secondary']],
            ],
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-features--feature239', $html);
        $this->assertStringContainsString('class="section-features__overlay-link" href="#collection"', $html);
        $this->assertStringContainsString('fa-chevron-up', $html);
        $this->assertStringContainsString('>Tout voir</span>', $html);
        $this->assertStringContainsString('section-features__btn--pill', $html);
    }

    public function testRendersIntegration3Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'integrations-3',
            'type' => 'integrations',
            'variant' => 'integration3',
            'content' => [
                'title' => 'Intégrations',
                'subtitle' => 'Connectez vos applications.',
                'items' => [
                    ['title' => 'Google Sheets', 'text' => 'Sync data.', 'url' => '/assets/sections/integrations/_shared/logos/google-icon.svg'],
                    ['title' => 'Slack', 'text' => 'Notifications.', 'url' => '/assets/sections/integrations/_shared/logos/slack-icon.svg'],
                ],
            ],
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-integrations--integration3', $html);
        $this->assertStringContainsString('Google Sheets', $html);
        $this->assertStringContainsString('section-integrations__row', $html);
    }

    public function testRendersIntegration9Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'integrations-9',
            'type' => 'integrations',
            'variant' => 'integration9',
            'content' => [
                'title' => 'Intégrations disponibles',
                'items' => [
                    ['title' => 'Figma', 'text' => 'Design sync.', 'url' => '/assets/sections/integrations/_shared/logos/figma-icon.svg'],
                ],
            ],
            'style' => ['bg' => 'background', 'padding' => 'lg'],
        ]);

        $this->assertStringContainsString('section-integrations--integration9', $html);
        $this->assertStringContainsString('section-integrations__card', $html);
        $this->assertStringContainsString('Figma', $html);
    }

    public function testRendersPricing2Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'pricing-2',
            'type' => 'pricing',
            'variant' => 'pricing2',
            'content' => SectionDefaults::content('pricing', 'pricing2'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-pricing--pricing2', $html);
        $this->assertStringContainsString('data-pricing-billing', $html);
        $this->assertStringContainsString('section-pricing__card--plan2', $html);
    }

    public function testRendersPricing6Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'pricing-6',
            'type' => 'pricing',
            'variant' => 'pricing6',
            'content' => SectionDefaults::content('pricing', 'pricing6'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-pricing--pricing6', $html);
        $this->assertStringContainsString('section-pricing__single-card', $html);
    }

    public function testRendersContact2Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'contact-2',
            'type' => 'contact',
            'variant' => 'contact2',
            'content' => SectionDefaults::content('contact', 'contact2'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-contact--contact2', $html);
        $this->assertStringContainsString('data-contact-form', $html);
        $this->assertStringContainsString('toolname="contact_message"', $html);
        $this->assertStringContainsString('section-contact__link', $html);
    }

    public function testRendersContact7Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'contact-7',
            'type' => 'contact',
            'variant' => 'contact7',
            'content' => SectionDefaults::content('contact', 'contact7'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-contact--contact7', $html);
        $this->assertStringContainsString('section-contact__card', $html);
        $this->assertStringContainsString('fa-envelope', $html);
        $this->assertStringContainsString('contact@exemple.fr', $html);
    }

    public function testRendersTestimonial4Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'testimonial-4',
            'type' => 'testimonials',
            'variant' => 'testimonial4',
            'content' => SectionDefaults::content('testimonials', 'testimonial4'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-testimonials--testimonial4', $html);
        $this->assertStringContainsString('section-testimonials__featured-card', $html);
        $this->assertStringContainsString('section-testimonials__card', $html);
    }

    public function testRendersTestimonial8Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'testimonial-8',
            'type' => 'testimonials',
            'variant' => 'testimonial8',
            'content' => SectionDefaults::content('testimonials', 'testimonial8'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-testimonials--testimonial8', $html);
        $this->assertStringContainsString('section-testimonials__masonry-item', $html);
        $this->assertStringContainsString('Témoignages', $html);
    }

    public function testRendersTestimonial9Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'testimonial-9',
            'type' => 'testimonials',
            'variant' => 'testimonial9',
            'content' => SectionDefaults::content('testimonials', 'testimonial9'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-testimonials--testimonial9', $html);
        $this->assertStringContainsString('section-testimonials__masonry-item', $html);
        $this->assertStringContainsString('section-testimonials__social-link', $html);
        $this->assertStringContainsString('fa-brands', $html);
    }

    public function testRendersTestimonial10Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'testimonial-10',
            'type' => 'testimonials',
            'variant' => 'testimonial10',
            'content' => SectionDefaults::content('testimonials', 'testimonial10'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-testimonials--testimonial10', $html);
        $this->assertStringContainsString('class="section-testimonials__single-quote"', $html);
        $this->assertStringContainsString('&ldquo;Cette bibliothèque de composants', $html);
        $this->assertStringContainsString('Camille Dupont', $html);
        $this->assertStringContainsString('avatars-webp/avatar-1.webp', $html);
        $this->assertStringNotContainsString('<blockquote', $html);
    }

    public function testRendersGallery4Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'gallery-4',
            'type' => 'gallery',
            'variant' => 'gallery4',
            'content' => SectionDefaults::content('gallery', 'gallery4'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-gallery--gallery4', $html);
        $this->assertStringContainsString('section-gallery__container--gallery4', $html);
        $this->assertStringContainsString('section-gallery__viewport--gallery4', $html);
        $this->assertStringContainsString('section-gallery__track--gallery4', $html);
        $this->assertStringContainsString('section-gallery__card--overlay', $html);
        $this->assertStringContainsString('section-gallery__dots--gallery4', $html);
        $this->assertStringContainsString('saas-hero-1-16x9.png', $html);
        $this->assertStringContainsString('Études de cas', $html);
    }

    public function testRendersGallery6Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'gallery-6',
            'type' => 'gallery',
            'variant' => 'gallery6',
            'content' => SectionDefaults::content('gallery', 'gallery6'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-gallery--gallery6', $html);
        $this->assertStringContainsString('section-gallery__demo-link', $html);
        $this->assertStringContainsString('section-gallery__card--stacked', $html);
        $this->assertStringContainsString('Réserver une démo', $html);
        $this->assertStringNotContainsString('section-gallery__dots', $html);
    }
}
