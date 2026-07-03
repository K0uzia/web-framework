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
                'variant' => 'centered',
                'visible' => true,
                'content' => ['title' => 'Bienvenue'],
                'style' => [],
            ],
            [
                'id' => 'hidden-1',
                'type' => 'cta',
                'variant' => 'banner',
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
                'id' => 'features-1',
                'type' => 'features',
                'variant' => 'grid-3',
                'visible' => true,
                'content' => [],
                'style' => [],
            ],
        ], [], true, ''));

        $html = LinkPicker::render('field-id', 'field_name', '', $this->pages);

        $this->assertStringContainsString('value="#features-1"', $html);
        $this->assertStringContainsString('Fonctionnalités', $html);
    }

    public function testPreservesCurrentValueInTextInput(): void
    {
        $html = LinkPicker::render('field-id', 'field_name', 'https://example.com', $this->pages);

        $this->assertStringContainsString('value="https://example.com"', $html);
    }
}
