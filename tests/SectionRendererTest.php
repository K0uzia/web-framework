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
        $this->assertStringContainsString('section-gallery__container--gallery6', $html);
        $this->assertStringContainsString('section-gallery__viewport--gallery6', $html);
        $this->assertStringContainsString('section-gallery__track--gallery6', $html);
        $this->assertStringContainsString('section-gallery__nav--gallery6', $html);
        $this->assertStringContainsString('section-gallery__nav-btn--gallery6', $html);
        $this->assertStringContainsString('section-gallery__card--stacked', $html);
        $this->assertStringContainsString('section-gallery__media-inner', $html);
        $this->assertStringContainsString('Réserver une démo', $html);
        $this->assertStringNotContainsString('section-gallery__dots', $html);
    }

    public function testRendersBlog7Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'blog-7',
            'type' => 'blog',
            'variant' => 'blog7',
            'content' => SectionDefaults::content('blog', 'blog7'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-blog--blog7', $html);
        $this->assertStringContainsString('section-blog__badge--blog7', $html);
        $this->assertStringContainsString('section-blog__grid--blog7', $html);
        $this->assertStringContainsString('section-blog__card--blog7', $html);
        $this->assertStringContainsString('Dernières actualités', $html);
        $this->assertStringContainsString('Premiers pas avec shadcn/ui', $html);
        $this->assertStringContainsString('Sarah Chen', $html);
        $this->assertStringContainsString('Lire l&#039;article', $html);
        $this->assertStringContainsString('lummi/bw12.jpeg', $html);
    }

    public function testRendersBlog8Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'blog-8',
            'type' => 'blog',
            'variant' => 'blog8',
            'content' => SectionDefaults::content('blog', 'blog8'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-blog--blog8', $html);
        $this->assertStringContainsString('section-blog__list--blog8', $html);
        $this->assertStringContainsString('section-blog__row--blog8', $html);
        $this->assertStringContainsString('section-blog__tags--blog8', $html);
        $this->assertStringContainsString('Web Design', $html);
        $this->assertStringContainsString('Interfaces modernes', $html);
        $this->assertStringContainsString('Michael Park', $html);
        $this->assertStringNotContainsString('section-blog__badge--blog7', $html);
    }

    public function testRendersDownload1Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'download-1',
            'type' => 'download',
            'variant' => 'download1',
            'content' => SectionDefaults::content('download', 'download1'),
            'style' => ['bg' => 'muted', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-download--download1', $html);
        $this->assertStringContainsString('section-download__grid', $html);
        $this->assertStringContainsString('section-download__card--desktop', $html);
        $this->assertStringContainsString('section-download__card--ios', $html);
        $this->assertStringContainsString('section-download__card--android', $html);
        $this->assertStringContainsString('Téléchargez notre application', $html);
        $this->assertStringContainsString('fa-desktop', $html);
        $this->assertStringContainsString('fa-download', $html);
        $this->assertStringContainsString('fa-brands fa-apple', $html);
        $this->assertStringContainsString('fa-brands fa-google-play', $html);
        $this->assertStringContainsString('section-download__store-btn--ios', $html);
        $this->assertStringContainsString('Télécharger sur l&#039;App Store', $html);
        $this->assertStringContainsString('rel="noopener noreferrer"', $html);
    }

    public function testRendersDownload2Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'download-2',
            'type' => 'download',
            'variant' => 'download2',
            'content' => SectionDefaults::content('download', 'download2'),
            'style' => ['bg' => 'muted', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-download--download2', $html);
        $this->assertStringContainsString('section-download__columns--minimal', $html);
        $this->assertStringContainsString('section-download__column--desktop', $html);
        $this->assertStringContainsString('section-download__icon-wrap--large', $html);
        $this->assertStringContainsString('section-download__column-title', $html);
        $this->assertStringContainsString('Téléchargement', $html);
        $this->assertStringContainsString('PC / Mac', $html);
        $this->assertStringContainsString('fa-brands fa-apple', $html);
        $this->assertStringNotContainsString('section-download__card--featured', $html);
    }

    public function testRendersTeam1Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'team-1',
            'type' => 'team',
            'variant' => 'team1',
            'content' => SectionDefaults::content('team', 'team1'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-team--team1', $html);
        $this->assertStringContainsString('section-team__grid--team1', $html);
        $this->assertStringContainsString('section-team__member--team1', $html);
        $this->assertStringContainsString('Sarah Chen', $html);
        $this->assertStringContainsString('PDG et fondatrice', $html);
        $this->assertStringContainsString('avatars-webp/avatar-1.webp', $html);
        $this->assertStringNotContainsString('section-team__social', $html);
    }

    public function testRendersTeam2Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'team-2',
            'type' => 'team',
            'variant' => 'team2',
            'content' => SectionDefaults::content('team', 'team2'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-team--team2', $html);
        $this->assertStringContainsString('section-team__member--team2', $html);
        $this->assertStringContainsString('section-team__social', $html);
        $this->assertStringContainsString('fa-brands fa-github', $html);
        $this->assertStringContainsString('fa-brands fa-linkedin', $html);
        $this->assertStringContainsString('Directeur technique', $html);
        $this->assertStringContainsString('section-team__role--team2', $html);
    }

    public function testRendersProjects5Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'projects-5',
            'type' => 'projects',
            'variant' => 'projects5',
            'content' => SectionDefaults::content('projects', 'projects5'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-projects--projects5', $html);
        $this->assertStringContainsString('section-projects__title--projects5', $html);
        $this->assertStringContainsString('section-projects__grid--projects5', $html);
        $this->assertStringContainsString('section-projects__card--projects5', $html);
        $this->assertStringContainsString('section-projects__year--projects5', $html);
        $this->assertStringContainsString('Projets', $html);
        $this->assertStringContainsString('Pavillon en béton moderne', $html);
        $this->assertStringContainsString('Architecture', $html);
        $this->assertStringContainsString('lummi/bw12.jpeg', $html);
    }

    public function testRendersTimeline3Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'timeline-3',
            'type' => 'timeline',
            'variant' => 'timeline3',
            'content' => SectionDefaults::content('timeline', 'timeline3'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-timeline--timeline3', $html);
        $this->assertStringContainsString('section-timeline__layout--timeline3', $html);
        $this->assertStringContainsString('section-timeline__card--timeline3', $html);
        $this->assertStringContainsString('section-timeline__buttons--timeline3', $html);
        $this->assertStringContainsString('Découvrez la différence avec nous', $html);
        $this->assertStringContainsString('Accompagnement dédié', $html);
        $this->assertStringContainsString('section-button--primary', $html);
        $this->assertStringContainsString('Commencer', $html);
    }

    public function testRendersChangelog1Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'changelog-1',
            'type' => 'changelog',
            'variant' => 'changelog1',
            'content' => SectionDefaults::content('changelog', 'changelog1'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-changelog--changelog1', $html);
        $this->assertStringContainsString('section-changelog__entry--changelog1', $html);
        $this->assertStringContainsString('section-changelog__badge--changelog1', $html);
        $this->assertStringContainsString('Version 1.3.0', $html);
        $this->assertStringContainsString('Tableau de bord analytique amélioré', $html);
        $this->assertStringContainsString('section-changelog__list--changelog1', $html);
        $this->assertStringContainsString('En savoir plus', $html);
        $this->assertStringContainsString('fa-arrow-up-right', $html);
    }

    public function testRendersProcess1Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'process-1',
            'type' => 'process',
            'variant' => 'process1',
            'content' => SectionDefaults::content('process', 'process1'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-process--process1', $html);
        $this->assertStringContainsString('section-process__layout--process1', $html);
        $this->assertStringContainsString('section-process__step--process1', $html);
        $this->assertStringContainsString('Notre processus', $html);
        $this->assertStringContainsString('Découverte et analyse', $html);
        $this->assertStringContainsString('fa-asterisk', $html);
        $this->assertStringContainsString('Nous contacter', $html);
        $this->assertStringContainsString('section-process__mark--process1', $html);
    }

    public function testRendersList2Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'list-2',
            'type' => 'list',
            'variant' => 'list2',
            'content' => SectionDefaults::content('list', 'list2'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-list--list2', $html);
        $this->assertStringContainsString('section-list__row--list2', $html);
        $this->assertStringContainsString('Nos réalisations et distinctions', $html);
        $this->assertStringContainsString('Reconnaissance sectorielle', $html);
        $this->assertStringContainsString('section-list__separator--list2', $html);
        $this->assertStringContainsString('Voir le projet', $html);
        $this->assertStringContainsString('fa-arrow-right', $html);
    }

    public function testRendersIndustries1Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'industry-1',
            'type' => 'industry',
            'variant' => 'industries1',
            'content' => SectionDefaults::content('industry', 'industries1'),
            'style' => ['bg' => 'background', 'padding' => 'lg'],
        ]);

        $this->assertStringContainsString('section-industry--industries1', $html);
        $this->assertStringContainsString('section-industry__card--industries1', $html);
        $this->assertStringContainsString('Santé', $html);
        $this->assertStringContainsString('fa-plus', $html);
        $this->assertStringContainsString('Aperçu', $html);
    }

    public function testRendersIndustries2Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'industry-2',
            'type' => 'industry',
            'variant' => 'industries2',
            'content' => SectionDefaults::content('industry', 'industries2'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-industry--industries2', $html);
        $this->assertStringContainsString('section-industry__badge--industries2', $html);
        $this->assertStringContainsString('section-industry__row--industries2', $html);
        $this->assertStringContainsString('Mines', $html);
        $this->assertStringContainsString('Secteurs', $html);
    }

    public function testRendersRateCard2Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'rate-card-1',
            'type' => 'rate-card',
            'variant' => 'rate-card2',
            'content' => SectionDefaults::content('rate-card', 'rate-card2'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-rate-card--rate-card2', $html);
        $this->assertStringContainsString('section-rate-card__card--rate-card2', $html);
        $this->assertStringContainsString('Retainer mensuel', $html);
        $this->assertStringContainsString('499', $html);
        $this->assertStringContainsString('€', $html);
        $this->assertStringContainsString('fa-bullseye', $html);
        $this->assertStringContainsString('Commencer', $html);
        $this->assertStringContainsString('section-rate-card__btn--rate-card2', $html);
    }

    public function testRendersTimeline9Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'timeline-9',
            'type' => 'timeline',
            'variant' => 'timeline9',
            'content' => SectionDefaults::content('timeline', 'timeline9'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-timeline--timeline9', $html);
        $this->assertStringContainsString('section-timeline__track--timeline9', $html);
        $this->assertStringContainsString('section-timeline__entry--timeline9', $html);
        $this->assertStringContainsString('section-timeline__dot--timeline9', $html);
        $this->assertStringContainsString('intelligence artificielle', $html);
        $this->assertStringContainsString('Naissance de l', $html);
        $this->assertStringContainsString('1956', $html);
        $this->assertStringNotContainsString('section-timeline__buttons--timeline3', $html);
    }

    public function testRendersLogos3Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'logos-3',
            'type' => 'logos',
            'variant' => 'logos3',
            'content' => SectionDefaults::content('logos', 'logos3'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-logos--logos3', $html);
        $this->assertStringContainsString('section-logos__title--logos3', $html);
        $this->assertStringContainsString('section-logos__marquee--logos3', $html);
        $this->assertStringContainsString('Ils nous font confiance', $html);
        $this->assertStringContainsString('fictional-company-logo-1.svg', $html);
    }

    public function testRendersLogos8Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'logos-8',
            'type' => 'logos',
            'variant' => 'logos8',
            'content' => SectionDefaults::content('logos', 'logos8'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-logos--logos8', $html);
        $this->assertStringContainsString('section-logos__grid--logos8', $html);
        $this->assertStringContainsString('section-logos__cell--logos8', $html);
        $this->assertStringNotContainsString('section-logos__marquee--logos8', $html);
    }

    public function testRendersLogos18Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'logos-18',
            'type' => 'logos',
            'variant' => 'logos18',
            'content' => SectionDefaults::content('logos', 'logos18'),
            'style' => ['bg' => 'background', 'padding' => 'md'],
        ]);

        $this->assertStringContainsString('section-logos--logos18', $html);
        $this->assertStringContainsString('section-logos__grid--logos18', $html);
        $this->assertStringNotContainsString('section-logos__title--logos18', $html);
    }

    public function testRendersLogos19Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'logos-19',
            'type' => 'logos',
            'variant' => 'logos19',
            'content' => SectionDefaults::content('logos', 'logos19'),
            'style' => ['bg' => 'background', 'padding' => 'lg'],
        ]);

        $this->assertStringContainsString('section-logos--logos19', $html);
        $this->assertStringContainsString('section-logos__marquee--logos19', $html);
        $this->assertStringContainsString('fictional-company-logo-12.svg', $html);
        $this->assertStringNotContainsString('section-logos__title--logos19', $html);
    }

    public function testRendersServices4Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'services-4',
            'type' => 'services',
            'variant' => 'services4',
            'content' => SectionDefaults::content('services', 'services4'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-services--services4', $html);
        $this->assertStringContainsString('section-services__grid--services4', $html);
        $this->assertStringContainsString('section-services__card--services4', $html);
        $this->assertStringContainsString('section-services__bullets--services4', $html);
        $this->assertStringContainsString('fa-gear', $html);
        $this->assertStringContainsString('Stratégie produit', $html);
        $this->assertStringContainsString('Étude de marché', $html);
    }

    public function testRendersServices12Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'services-12',
            'type' => 'services',
            'variant' => 'services12',
            'content' => SectionDefaults::content('services', 'services12'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-services--services12', $html);
        $this->assertStringContainsString('section-services__layout--services12', $html);
        $this->assertStringContainsString('section-services__featured--services12', $html);
        $this->assertStringContainsString('section-services__secondary--services12', $html);
        $this->assertStringContainsString('section-services__card--featured--services12', $html);
        $this->assertStringContainsString('fa-arrow-up-right', $html);
        $this->assertStringContainsString('Services à la une', $html);
        $this->assertStringContainsString('Développement web', $html);
        $this->assertStringContainsString('lummi/bw12.jpeg', $html);
    }

    public function testRendersCompare7Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'compare-7',
            'type' => 'compare',
            'variant' => 'compare7',
            'content' => SectionDefaults::content('compare', 'compare7'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-compare--compare7', $html);
        $this->assertStringContainsString('section-compare__table--compare7', $html);
        $this->assertStringContainsString('Notre solution', $html);
        $this->assertStringContainsString('Système de design', $html);
        $this->assertStringContainsString('section-compare__tooltip-trigger--compare7', $html);
        $this->assertStringContainsString('Kit Figma', $html);
    }

    public function testRendersCompare8Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'compare-8',
            'type' => 'compare',
            'variant' => 'compare8',
            'content' => SectionDefaults::content('compare', 'compare8'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-compare--compare8', $html);
        $this->assertStringContainsString('section-compare__panel--compare8', $html);
        $this->assertStringContainsString('section-compare__row--compare8', $html);
        $this->assertStringContainsString('fa-check', $html);
        $this->assertStringContainsString('fa-xmark', $html);
        $this->assertStringContainsString('Comparez-nous', $html);
        $this->assertStringContainsString('section-compare__dash--compare8', $html);
    }

    public function testRendersCta4Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'cta-4',
            'type' => 'cta',
            'variant' => 'cta4',
            'content' => SectionDefaults::content('cta', 'cta4'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-cta--cta4', $html);
        $this->assertStringContainsString('section-cta__features--cta4', $html);
        $this->assertStringContainsString('fa-check', $html);
        $this->assertStringContainsString('fa-arrow-right', $html);
        $this->assertStringContainsString('Intégration simple', $html);
        $this->assertStringContainsString('Passez à l&#039;action', $html);
    }

    public function testRendersCta10Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'cta-10',
            'type' => 'cta',
            'variant' => 'cta10',
            'content' => SectionDefaults::content('cta', 'cta10'),
            'style' => ['bg' => 'background', 'padding' => 'lg'],
        ]);

        $this->assertStringContainsString('section-cta--cta10', $html);
        $this->assertStringContainsString('section-cta__panel--cta10', $html);
        $this->assertStringContainsString('Planifier une démo', $html);
        $this->assertStringContainsString('section-button--secondary', $html);
    }

    public function testRendersCta11Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'cta-11',
            'type' => 'cta',
            'variant' => 'cta11',
            'content' => SectionDefaults::content('cta', 'cta11'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-cta--cta11', $html);
        $this->assertStringContainsString('section-cta__image--cta11', $html);
        $this->assertStringContainsString('section-cta__badge--cta11', $html);
        $this->assertStringContainsString('fa-wand-magic-sparkles', $html);
        $this->assertStringContainsString('saas-hero-1-16x9.png', $html);
    }

    public function testRendersCta38Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'cta-38',
            'type' => 'cta',
            'variant' => 'cta38',
            'content' => SectionDefaults::content('cta', 'cta38'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-cta--cta38', $html);
        $this->assertStringContainsString('section-cta__panel--cta38', $html);
        $this->assertStringContainsString('Accéder', $html);
    }

    public function testRendersAwards1Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'awards-1',
            'type' => 'awards',
            'variant' => 'awards1',
            'content' => SectionDefaults::content('awards', 'awards1'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-awards--awards1', $html);
        $this->assertStringContainsString('section-awards__table--awards1', $html);
        $this->assertStringContainsString('CSS Design Awards', $html);
        $this->assertStringContainsString('noopener noreferrer', $html);
        $this->assertStringContainsString('Récompenses', $html);
    }

    public function testRendersAwards2Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'awards-2',
            'type' => 'awards',
            'variant' => 'awards2',
            'content' => SectionDefaults::content('awards', 'awards2'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-awards--awards2', $html);
        $this->assertStringContainsString('section-awards__list--awards2', $html);
        $this->assertStringContainsString('section-awards__marker--awards2', $html);
        $this->assertStringContainsString('Prix de la collaboration créative', $html);
        $this->assertStringContainsString('2019', $html);
    }

    public function testRendersCommunity1Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'community-1',
            'type' => 'community',
            'variant' => 'community1',
            'content' => SectionDefaults::content('community', 'community1'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-community--community1', $html);
        $this->assertStringContainsString('section-community__logo--community1', $html);
        $this->assertStringContainsString('block-1.svg', $html);
        $this->assertStringContainsString('fa-brands fa-x-twitter', $html);
        $this->assertStringContainsString('fa-brands fa-github', $html);
        $this->assertStringContainsString('fa-brands fa-discord', $html);
        $this->assertStringContainsString('noopener noreferrer', $html);
        $this->assertStringContainsString('designers et développeurs', $html);
    }

    public function testRendersCommunity2Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'community-2',
            'type' => 'community',
            'variant' => 'community2',
            'content' => SectionDefaults::content('community', 'community2'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-community--community2', $html);
        $this->assertStringContainsString('section-community__grid--community2', $html);
        $this->assertStringContainsString('section-community__card--community2', $html);
        $this->assertStringContainsString('fa-arrow-up-right', $html);
        $this->assertStringContainsString('LinkedIn', $html);
        $this->assertStringContainsString('Contribuez à nos projets open source', $html);
    }

    public function testRendersStats6Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'stats-6',
            'type' => 'stats',
            'variant' => 'stats6',
            'content' => SectionDefaults::content('stats', 'stats6'),
            'style' => ['bg' => 'muted', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-stats--stats6', $html);
        $this->assertStringContainsString('section-stats__grid--stats6', $html);
        $this->assertStringContainsString('90%', $html);
        $this->assertStringContainsString('Indicateur 1', $html);
        $this->assertStringContainsString('Commencer', $html);
        $this->assertStringContainsString('section-button--secondary', $html);
    }

    public function testRendersStats8Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'stats-8',
            'type' => 'stats',
            'variant' => 'stats8',
            'content' => SectionDefaults::content('stats', 'stats8'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-stats--stats8', $html);
        $this->assertStringContainsString('section-stats__grid--stats8', $html);
        $this->assertStringContainsString('250%+', $html);
        $this->assertStringContainsString('fa-arrow-right', $html);
        $this->assertStringContainsString('rapport d&#039;impact complet', $html);
    }

    public function testRendersCareers1Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'careers-1',
            'type' => 'careers',
            'variant' => 'careers1',
            'content' => SectionDefaults::content('careers', 'careers1'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-careers--careers1', $html);
        $this->assertStringContainsString('section-careers__department--careers1', $html);
        $this->assertStringContainsString('Responsable commercial', $html);
        $this->assertStringContainsString('Customer Success', $html);
        $this->assertStringContainsString('fa-arrow-right', $html);
    }

    public function testRendersCareers4Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'careers-4',
            'type' => 'careers',
            'variant' => 'careers4',
            'content' => SectionDefaults::content('careers', 'careers4'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-careers--careers4', $html);
        $this->assertStringContainsString('section-careers__category--careers4', $html);
        $this->assertStringContainsString('Développeur frontend senior', $html);
        $this->assertStringContainsString('Product designer', $html);
        $this->assertStringContainsString('Ingénierie', $html);
    }

    public function testRendersFaq1Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'faq-1',
            'type' => 'faq',
            'variant' => 'faq1',
            'content' => SectionDefaults::content('faq', 'faq1'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-faq--faq1', $html);
        $this->assertStringContainsString('<details', $html);
        $this->assertStringContainsString('<summary', $html);
        $this->assertStringContainsString('Qu&#039;est-ce qu&#039;une FAQ ?', $html);
    }

    public function testRendersFaq3Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'faq-3',
            'type' => 'faq',
            'variant' => 'faq3',
            'content' => SectionDefaults::content('faq', 'faq3'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-faq--faq3', $html);
        $this->assertStringContainsString('section-faq__head--faq3', $html);
        $this->assertStringContainsString('Contactez notre équipe support', $html);
        $this->assertStringContainsString('section-faq__accordion--faq3', $html);
    }

    public function testRendersFaq5Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'faq-5',
            'type' => 'faq',
            'variant' => 'faq5',
            'content' => SectionDefaults::content('faq', 'faq5'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-faq--faq5', $html);
        $this->assertStringContainsString('section-faq__badge--faq5', $html);
        $this->assertStringContainsString('section-faq__number--faq5', $html);
        $this->assertStringContainsString('Questions et réponses courantes', $html);
        $this->assertStringContainsString('Quels sont les avantages', $html);
    }

    public function testRendersCodeexample1Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'code-1',
            'type' => 'code',
            'variant' => 'codeexample1',
            'content' => SectionDefaults::content('code', 'codeexample1'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-code--codeexample1', $html);
        $this->assertStringContainsString('section-code__switcher--codeexample1', $html);
        $this->assertStringContainsString('data-code-example', $html);
        $this->assertStringContainsString('utils.js', $html);
        $this->assertStringContainsString('utils.py', $html);
        $this->assertStringContainsString('fa-arrow-up-right', $html);
        $this->assertStringContainsString('data-code-copy', $html);
        $this->assertStringContainsString('ÉCRIVEZ DU CODE.', $html);
        $this->assertStringContainsString('function fibonacci', $html);
    }

    public function testRendersCompliance1Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'compliance-1',
            'type' => 'compliance',
            'variant' => 'compliance1',
            'content' => SectionDefaults::content('compliance', 'compliance1'),
            'style' => ['bg' => 'muted', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-compliance--compliance1', $html);
        $this->assertStringContainsString('section-compliance__status--compliance1', $html);
        $this->assertStringContainsString('section-compliance__panel--compliance1', $html);
        $this->assertStringContainsString('GDPR.svg', $html);
        $this->assertStringContainsString('ISO-27001.svg', $html);
        $this->assertStringContainsString('Pistes d&#039;audit automatisées', $html);
        $this->assertStringContainsString('Conformité et sécurité complètes', $html);
    }

    public function testRendersCasestudies2Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'case-study-2',
            'type' => 'case-study',
            'variant' => 'casestudies2',
            'content' => SectionDefaults::content('case-study', 'casestudies2'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-case-study--casestudies2', $html);
        $this->assertStringContainsString('section-case-study__quote--casestudies2', $html);
        $this->assertStringContainsString('Michael Rivera', $html);
        $this->assertStringContainsString('98 %', $html);
        $this->assertStringContainsString('fictional-company-logo-2.svg', $html);
    }

    public function testRendersCasestudies3Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'case-study-3',
            'type' => 'case-study',
            'variant' => 'casestudies3',
            'content' => SectionDefaults::content('case-study', 'casestudies3'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-case-study--casestudies3', $html);
        $this->assertStringContainsString('section-case-study__card--featured--casestudies3', $html);
        $this->assertStringContainsString('Lire l&#039;étude de cas', $html);
        $this->assertStringContainsString('Automatisation des flux', $html);
        $this->assertStringContainsString('block-2.svg', $html);
    }

    public function testRendersBookademo1Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'demo-1',
            'type' => 'demo',
            'variant' => 'bookademo1',
            'content' => SectionDefaults::content('demo', 'bookademo1'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-demo--bookademo1', $html);
        $this->assertStringContainsString('section-demo__form--bookademo1', $html);
        $this->assertStringContainsString('toolname="demo_booking"', $html);
        $this->assertStringContainsString('section-demo__marquee--bookademo1', $html);
        $this->assertStringContainsString('Simplifiez votre flux de développement', $html);
        $this->assertStringContainsString('fictional-company-logo-1.svg', $html);
    }

    public function testRendersBookademo2Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'demo-2',
            'type' => 'demo',
            'variant' => 'bookademo2',
            'content' => SectionDefaults::content('demo', 'bookademo2'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-demo--bookademo2', $html);
        $this->assertStringContainsString('section-demo__form--bookademo2', $html);
        $this->assertStringContainsString('data-demo-testimonials', $html);
        $this->assertStringContainsString('Planifier une démo', $html);
        $this->assertStringContainsString('contactez notre équipe', $html);
        $this->assertStringContainsString('Alex Chen', $html);
        $this->assertStringContainsString('Plébiscité par les équipes', $html);
    }

    public function testRendersExperience1Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'experience-1',
            'type' => 'experience',
            'variant' => 'experience1',
            'content' => SectionDefaults::content('experience', 'experience1'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-experience--experience1', $html);
        $this->assertStringContainsString('section-experience__entry--experience1', $html);
        $this->assertStringContainsString('Télécharger le CV', $html);
        $this->assertStringContainsString('fa-download', $html);
        $this->assertStringContainsString('Ingénieur logiciel senior', $html);
        $this->assertStringContainsString('google-icon.svg', $html);
        $this->assertStringContainsString('netflix-icon.svg', $html);
    }

    public function testRendersWaitlist1Section(): void
    {
        $html = $this->renderer->renderOne([
            'id' => 'waitlist-1',
            'type' => 'waitlist',
            'variant' => 'waitlist1',
            'content' => SectionDefaults::content('waitlist', 'waitlist1'),
            'style' => ['bg' => 'background', 'padding' => 'xl'],
        ]);

        $this->assertStringContainsString('section-waitlist--waitlist1', $html);
        $this->assertStringContainsString('section-waitlist__form--waitlist1', $html);
        $this->assertStringContainsString('toolname="waitlist_signup"', $html);
        $this->assertStringContainsString('data-contact-form', $html);
        $this->assertStringContainsString('Rejoignez la liste d&#039;attente', $html);
        $this->assertStringContainsString('section-waitlist__lines-svg--waitlist1', $html);
        $this->assertStringContainsString('section-waitlist__line-path--waitlist1', $html);
        $this->assertStringContainsString('avatar-1.png', $html);
        $this->assertStringContainsString('+ de 1 000 personnes', $html);
    }
}
