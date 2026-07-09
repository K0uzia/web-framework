<?php

declare(strict_types=1);

namespace Tests\Http\Dev;

use App\Http\Dev\LinkPicker;
use Capsule\Page;
use Capsule\PageRepository;
use PHPUnit\Framework\TestCase;

final class LinkPickerTest extends TestCase
{
    private PageRepository $pages;

    protected function setUp(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__, 3) . '/migrations/sqlite_init.sql') ?: '');
        $this->pages = new PageRepository($pdo);
    }

    public function testListsPublishedPagesAndSkipsDrafts(): void
    {
        $this->pages->save(new Page('', 'Home', 'default', '', [], [], true, ''));
        $this->pages->save(new Page('draft', 'Draft', 'default', '', [], [], false, ''));

        $html = LinkPicker::render('field-id', 'field_name', '', $this->pages);

        $this->assertStringContainsString('Accueil', $html);
        $this->assertStringNotContainsString('Draft', $html);
    }

    public function testListsAnchoredSectionsPerPageGroupedByOptgroup(): void
    {
        $this->pages->save(new Page('about', 'About', 'default', '', [
            [
                'id' => 'hero-1',
                'type' => 'hero',
                'variant' => 'hero3',
                'visible' => true,
                'content' => ['title' => 'Bienvenue'],
                'style' => [],
            ],
            [
                'id' => 'hidden-1',
                'type' => 'hero',
                'variant' => 'hero3',
                'visible' => false,
                'content' => [],
                'style' => [],
            ],
        ], [], true, ''));

        $html = LinkPicker::render('field-id', 'field_name', '', $this->pages);

        $this->assertStringContainsString('<optgroup label="Sections : /about : About">', $html);
        $this->assertStringContainsString('value="/about#hero-1"', $html);
        $this->assertStringContainsString('Bienvenue', $html);
        $this->assertStringNotContainsString('hidden-1', $html);
    }

    public function testFallsBackToTypeLabelWhenSectionHasNoTitle(): void
    {
        $this->pages->save(new Page('', 'Home', 'default', '', [
            [
                'id' => 'hero-2',
                'type' => 'hero',
                'variant' => 'hero3',
                'visible' => true,
                'content' => [],
                'style' => [],
            ],
        ], [], true, ''));

        $html = LinkPicker::render('field-id', 'field_name', '', $this->pages);

        $this->assertStringContainsString('value="#hero-2"', $html);
        $this->assertStringContainsString('Hero', $html);
    }

    public function testPreservesCurrentValueInTextInput(): void
    {
        $html = LinkPicker::render('field-id', 'field_name', 'https://example.com', $this->pages);

        $this->assertStringContainsString('value="https://example.com"', $html);
    }

    public function testSelectHasValidLinkPickerAttributeForJavascript(): void
    {
        $html = LinkPicker::render('field-id', 'field_name', '', $this->pages, 'chrome-variant-form');

        $this->assertStringContainsString('data-link-picker-select', $html);
        $this->assertStringNotContainsString('data-link-picker-select"', $html);
        $this->assertStringContainsString('form="chrome-variant-form"', $html);
    }
}
