<?php

declare(strict_types=1);

namespace Tests;

use Capsule\ClientDashboardConfig;
use PHPUnit\Framework\TestCase;

final class ClientDashboardConfigTest extends TestCase
{
    public function testNormalizeDropsEmptySectionsAndUnknownShapes(): void
    {
        $normalized = ClientDashboardConfig::normalize([
            'pages' => [
                'contact' => [
                    'sections' => [
                        'hero-1' => ['fields' => ['title', 'title', '']],
                        'hero-2' => ['fields' => []],
                    ],
                ],
                'ghost' => ['sections' => []],
            ],
        ]);

        $this->assertSame(
            [
                'medias_enabled' => false,
                'pages' => [
                    'contact' => [
                        'sections' => [
                            'hero-1' => ['fields' => ['title']],
                        ],
                    ],
                ],
            ],
            $normalized,
        );
    }

    public function testFormFieldKeyRoundTrip(): void
    {
        $key = ClientDashboardConfig::formFieldKey('', 'hero-abc', 'title');
        $this->assertSame('cd::hero-abc:title', $key);
        $this->assertSame(['', 'hero-abc', 'title'], ClientDashboardConfig::parseFormFieldKey($key));

        $key2 = ClientDashboardConfig::formFieldKey('about', 'cta-1', 'subtitle');
        $this->assertSame(['about', 'cta-1', 'subtitle'], ClientDashboardConfig::parseFormFieldKey($key2));
    }

    public function testFromFormDataFiltersBySectionIndex(): void
    {
        $config = ClientDashboardConfig::fromFormData(
            [
                ClientDashboardConfig::formFieldKey('', 'hero-1', 'title') => '1',
                ClientDashboardConfig::formFieldKey('', 'hero-1', 'hack') => '1',
                ClientDashboardConfig::formFieldKey('missing', 'hero-1', 'title') => '1',
            ],
            [
                'hero-1' => ['type' => 'hero', 'fields' => ['title', 'subtitle']],
            ],
            ['', 'about'],
        );

        $this->assertSame(['title'], ClientDashboardConfig::allowedFields($config, '', 'hero-1'));
        $this->assertFalse(ClientDashboardConfig::isPageEditable($config, 'missing'));
        $this->assertFalse(ClientDashboardConfig::isMediasEnabled($config));
    }

    public function testFromFormDataPersistsMediasFlag(): void
    {
        $config = ClientDashboardConfig::fromFormData(
            [ClientDashboardConfig::FORM_MEDIAS_KEY => '1'],
            [],
            [''],
        );

        $this->assertTrue(ClientDashboardConfig::isMediasEnabled($config));
        $this->assertSame([], $config['pages']);
    }
}
