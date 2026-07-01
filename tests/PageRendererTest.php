<?php

declare(strict_types=1);

namespace Tests;

use Capsule\Http\Factory\ResponseFactory;
use Capsule\PageRenderer;
use Capsule\View;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PageRenderer::class)]
final class PageRendererTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/capsule-render-' . bin2hex(random_bytes(4));
        mkdir($this->root . '/layouts');
        mkdir($this->root . '/pages');

        file_put_contents($this->root . '/layouts/default.html', '<html><body>{{{content}}}</body></html>');
        file_put_contents(
            $this->root . '/pages/home.html',
            "<h1>{{title}}</h1>\n"
        );
        file_put_contents($this->root . '/pages/home.yaml', "title: <unsafe>\nlayout: default\n");
    }

    protected function tearDown(): void
    {
        @unlink($this->root . '/pages/home.html');
        @unlink($this->root . '/pages/home.yaml');
        @unlink($this->root . '/layouts/default.html');
        @rmdir($this->root . '/pages');
        @rmdir($this->root . '/layouts');
        @rmdir($this->root);
    }

    public function testEscapesVariablesInRenderedPage(): void
    {
        $view = new View($this->root . '/layouts', '');
        $renderer = new PageRenderer(new ResponseFactory(), $view, $this->root . '/layouts', 'http://localhost:8080');

        $response = $renderer->render($this->root . '/pages/home.html');
        $body = (string) $response->getBody();

        $this->assertStringContainsString('<h1>&lt;unsafe&gt;</h1>', $body);
        $this->assertStringNotContainsString('<h1><unsafe></h1>', $body);
    }

    public function testExternalYamlOverridesInlineFrontmatter(): void
    {
        file_put_contents($this->root . '/pages/mix.yaml', "title: From YAML\n");
        file_put_contents($this->root . '/pages/mix.html', "---\ntitle: From HTML\n---\n<p>{{title}}</p>\n");

        $view = new View($this->root . '/layouts', '');
        $renderer = new PageRenderer(new ResponseFactory(), $view, $this->root . '/layouts', 'http://localhost:8080');
        $response = $renderer->render($this->root . '/pages/mix.html');

        $this->assertStringContainsString('<p>From YAML</p>', (string) $response->getBody());

        @unlink($this->root . '/pages/mix.yaml');
        @unlink($this->root . '/pages/mix.html');
    }

    public function testRendersSeoMetadataAndJsonLd(): void
    {
        file_put_contents($this->root . '/pages/seo.yaml', "title: SEO Test\ndescription: Meta desc\n");
        file_put_contents($this->root . '/pages/seo.html', '<p>body</p>');

        $view = new View($this->root . '/layouts', '');
        $renderer = new PageRenderer(new ResponseFactory(), $view, $this->root . '/layouts', 'https://example.com');
        $body = (string) $renderer->render($this->root . '/pages/seo.html', [], '/seo')->getBody();

        $this->assertStringContainsString('<meta name="description" content="Meta desc"', $body);
        $this->assertStringContainsString('<link rel="canonical" href="https://example.com/seo"', $body);
        $this->assertStringContainsString('<script type="application/ld+json">', $body);
        $this->assertStringContainsString('"@type":"WebPage"', str_replace(' ', '', $body));

        @unlink($this->root . '/pages/seo.yaml');
        @unlink($this->root . '/pages/seo.html');
    }
}
