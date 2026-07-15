<?php

declare(strict_types=1);

namespace Tests\Section;

use Capsule\Section\SectionFieldSchema;
use Capsule\SectionRegistry;
use PHPUnit\Framework\TestCase;

final class SectionFieldSchemaTest extends TestCase
{
    private SectionFieldSchema $schema;

    protected function setUp(): void
    {
        $root = dirname(__DIR__, 2);
        $registry = new SectionRegistry(
            $root . '/resources/sections/registry.yaml',
            $root . '/resources/sections/_shared/style-fields.yaml',
        );
        $this->schema = new SectionFieldSchema(
            $registry,
            $root . '/resources/sections/_shared/variant-content-overrides.yaml',
        );
    }

    public function testContactVariantFieldsAreFilteredByLabelHint(): void
    {
        $contact2 = $this->schema->contentFieldsForVariant('contact', 'contact2');

        $this->assertArrayHasKey('form_heading', $contact2);
        $this->assertArrayNotHasKey('chat_label', $contact2);
    }

    public function testFeatureOverlayFieldsOnlyForFeature239(): void
    {
        $feature3 = $this->schema->contentFieldsForVariant('features', 'feature3');
        $feature239 = $this->schema->contentFieldsForVariant('features', 'feature239');

        $this->assertArrayNotHasKey('overlay_title', $feature3);
        $this->assertArrayHasKey('overlay_title', $feature239);
    }

    public function testHeroVariantFieldsAreFiltered(): void
    {
        $hero7 = $this->schema->contentFieldsForVariant('hero', 'hero7');
        $hero78 = $this->schema->contentFieldsForVariant('hero', 'hero78');

        $this->assertArrayHasKey('title', $hero7);
        $this->assertArrayHasKey('reviews_rating', $hero7);
        $this->assertArrayNotHasKey('background_image_url', $hero7);
        $this->assertArrayHasKey('background_type', $hero78);
        $this->assertArrayHasKey('background_image_url', $hero78);
        $this->assertArrayHasKey('background_video_url', $hero78);
        $this->assertArrayHasKey('background_shader_id', $hero78);
        $this->assertArrayNotHasKey('items', $hero7);
    }

    public function testUnflattenFormKeepsUnknownVariantSpecificKeys(): void
    {
        $existing = ['chat_label' => 'Chat', 'title' => 'Ancien'];
        $data = [
            'content_title' => 'Nouveau',
            'content_subtitle' => 'Sous-titre',
        ];

        $content = $this->schema->unflattenForm($data, $existing, 'contact', 'contact2');

        $this->assertSame('Nouveau', $content['title']);
        $this->assertSame('Chat', $content['chat_label']);
    }

    public function testUnflattenFormParsesGenericRepeaters(): void
    {
        $data = [
            'content_review_avatars_0_url' => '/uploads/a.png',
            'content_review_avatars_0_title' => 'Alice',
            'content_review_avatars_1_url' => '/uploads/b.png',
            'content_review_avatars_1_title' => 'Bob',
            'content_logos_0_url' => '/uploads/logo.png',
            'content_logos_0_label' => 'ISO',
        ];

        $hero = $this->schema->unflattenForm($data, [], 'hero', 'hero7');
        $this->assertCount(2, $hero['review_avatars'] ?? []);
        $this->assertSame('Alice', $hero['review_avatars'][0]['title'] ?? '');

        $compliance = $this->schema->unflattenForm($data, [], 'compliance', 'compliance1');
        $this->assertCount(1, $compliance['logos'] ?? []);
        $this->assertSame('ISO', $compliance['logos'][0]['label'] ?? '');
    }

    public function testUnflattenFormParsesButtonsRepeater(): void
    {
        $data = [
            'content_buttons_count' => '2',
            'content_buttons_0_label' => 'Commencer',
            'content_buttons_0_href' => '/signup',
            'content_buttons_0_style' => 'primary',
            'content_buttons_1_label' => 'En savoir plus',
            'content_buttons_1_href' => '/about',
            'content_buttons_1_style' => 'secondary',
        ];

        $content = $this->schema->unflattenForm($data, [], 'stats', 'stats6');

        $this->assertCount(2, $content['buttons'] ?? []);
        $this->assertSame('Commencer', $content['buttons'][0]['label'] ?? '');
        $this->assertSame('secondary', $content['buttons'][1]['style'] ?? '');
    }

    public function testButtonsRepeaterParsesOutlineStyle(): void
    {
        $data = [
            'content_buttons_count' => '1',
            'content_buttons_0_label' => 'Contact',
            'content_buttons_0_href' => '/contact',
            'content_buttons_0_style' => 'outline',
        ];

        $content = $this->schema->unflattenForm($data, [], 'stats', 'stats6');

        $this->assertSame('outline', $content['buttons'][0]['style'] ?? '');
    }
}
