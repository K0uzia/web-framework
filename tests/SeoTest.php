<?php

declare(strict_types=1);

namespace Tests;

use Capsule\Seo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Seo::class)]
final class SeoTest extends TestCase
{
    public function testJsonLdProducesValidJson(): void
    {
        $json = Seo::jsonLd([
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => 'Test "page"',
        ]);

        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertSame('WebPage', $decoded['@type']);
        $this->assertSame('Test "page"', $decoded['name']);
    }

    public function testApplyBuildsCanonicalAndJsonLd(): void
    {
        $data = Seo::apply(
            ['title' => 'Accueil', 'description' => 'Page d\'accueil'],
            '/',
            'https://example.com'
        );

        $this->assertSame('https://example.com/', $data['canonical']);
        $this->assertIsString($data['json_ld']);
        $this->assertStringContainsString('"@type":"WebPage"', str_replace(' ', '', $data['json_ld']));
    }

    public function testApplyEncodesCustomJsonLdFromArray(): void
    {
        $data = Seo::apply(
            [
                'title' => 'Blog',
                'json_ld' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'Article',
                    'headline' => 'Mon article',
                ],
            ],
            '/blog/post',
            'https://example.com'
        );

        $this->assertStringContainsString('"@type":"Article"', str_replace(' ', '', $data['json_ld']));
    }

    public function testApplyBuildsJsonLdFromSchemaFields(): void
    {
        $data = Seo::apply(
            [
                'title' => 'Fallback titre',
                'description' => 'Résumé',
                'schema_type' => 'Article',
                'schema_name' => 'Titre schema',
                'schema_headline' => 'Titre schema',
                'schema_datePublished' => '2024-06-01',
            ],
            '/article/demo',
            'https://example.com'
        );

        $decoded = json_decode($data['json_ld'], true);
        $this->assertIsArray($decoded);
        $this->assertSame('Article', $decoded['@type']);
        $this->assertSame('Titre schema', $decoded['name']);
        $this->assertSame('Titre schema', $decoded['headline']);
        $this->assertSame('2024-06-01', $decoded['datePublished']);
        $this->assertSame('https://example.com/article/demo', $decoded['url']);

        $this->assertArrayNotHasKey('schema_type', $data);
        $this->assertArrayNotHasKey('schema_name', $data);
        $this->assertArrayNotHasKey('schema_headline', $data);
    }

    public function testSchemaNameDefaultsToTitle(): void
    {
        $data = Seo::apply(
            ['title' => 'Mon titre', 'schema_type' => 'WebPage'],
            '/',
            'https://example.com'
        );

        $decoded = json_decode($data['json_ld'], true);
        $this->assertSame('Mon titre', $decoded['name']);
    }

    public function testFullJsonLdOverridesSchemaFields(): void
    {
        $data = Seo::apply(
            [
                'schema_type' => 'WebPage',
                'json_ld' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'FAQPage',
                    'name' => 'FAQ',
                ],
            ],
            '/faq',
            'https://example.com'
        );

        $this->assertStringContainsString('"@type":"FAQPage"', str_replace(' ', '', $data['json_ld']));
    }
}
