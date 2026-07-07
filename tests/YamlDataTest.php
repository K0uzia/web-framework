<?php

declare(strict_types=1);

namespace Tests;

use Capsule\YamlData;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(YamlData::class)]
final class YamlDataTest extends TestCase
{
    public function testParsesScalars(): void
    {
        $data = YamlData::parse(<<<'YAML'
title: Accueil
layout: default
message: Bonjour le monde
YAML);

        $this->assertSame('Accueil', $data['title']);
        $this->assertSame('default', $data['layout']);
        $this->assertSame('Bonjour le monde', $data['message']);
    }

    public function testParsesNestedBlock(): void
    {
        $data = YamlData::parse(<<<'YAML'
title: Contact
cta:
  label: En savoir plus
  href: /about
YAML);

        $this->assertSame('Contact', $data['title']);
        $this->assertIsArray($data['cta']);
        $this->assertSame('En savoir plus', $data['cta']['label']);
        $this->assertSame('/about', $data['cta']['href']);
    }

    public function testParsesHyphenatedKeys(): void
    {
        $data = YamlData::parse(<<<'YAML'
features:
  variants:
    grid-3:
      label: Grille 3 colonnes
YAML);

        $this->assertIsArray($data['features']);
        $this->assertIsArray($data['features']['variants']);
        $this->assertArrayHasKey('feature-1', $data['features']['variants']);
        $this->assertSame('Feature 1', $data['features']['variants']['feature-1']['label']);
    }

    public function testSiblingDataFile(): void
    {
        $dir = sys_get_temp_dir() . '/capsule-yaml-' . bin2hex(random_bytes(4));
        mkdir($dir);
        file_put_contents($dir . '/about.html', '<p></p>');
        file_put_contents($dir . '/about.yaml', "title: About\n");

        $this->assertSame($dir . '/about.yaml', YamlData::siblingDataFile($dir . '/about.html'));

        @unlink($dir . '/about.html');
        @unlink($dir . '/about.yaml');
        @rmdir($dir);
    }
}
