<?php

declare(strict_types=1);

namespace Tests;

use Capsule\Frontmatter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Frontmatter::class)]
final class FrontmatterTest extends TestCase
{
    public function testParsesYamlAndBody(): void
    {
        $raw = <<<'RAW'
---
title: Accueil
layout: default
---
<h1>{{title}}</h1>
RAW;

        $parsed = Frontmatter::parse($raw);

        $this->assertSame('Accueil', $parsed['meta']['title']);
        $this->assertSame('default', $parsed['meta']['layout']);
        $this->assertSame('<h1>{{title}}</h1>', $parsed['body']);
    }

    public function testReturnsRawBodyWhenNoFrontmatter(): void
    {
        $parsed = Frontmatter::parse('<p>Hello</p>');

        $this->assertSame([], $parsed['meta']);
        $this->assertSame('<p>Hello</p>', $parsed['body']);
    }
}
