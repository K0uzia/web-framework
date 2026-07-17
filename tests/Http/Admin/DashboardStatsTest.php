<?php

declare(strict_types=1);

namespace Tests\Http\Admin;

use App\Http\Admin\DashboardStats;
use App\Http\Admin\PageRowsBuilder;
use Capsule\MediaRepository;
use Capsule\Page;
use Capsule\PageRepository;
use Capsule\SiteRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DashboardStats::class)]
final class DashboardStatsTest extends TestCase
{
    public function testBuildCountsPagesArticlesAndClientMedias(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__, 3) . '/migrations/sqlite_init.sql') ?: '');
        $site = new SiteRepository($pdo);
        $pages = new PageRepository($pdo);
        $media = new MediaRepository($pdo);

        $pages->save(new Page(
            '',
            'Accueil',
            'default',
            '',
            [[
                'id' => 'blog-1',
                'type' => 'blog',
                'variant' => 'blog7',
                'visible' => true,
                'content' => [
                    'items' => [
                        ['title' => 'A'],
                        ['title' => 'B'],
                        ['title' => 'C'],
                    ],
                ],
                'style' => [],
            ]],
            [],
            true,
            '',
        ));
        $site->setClientDashboard([
            'medias_enabled' => true,
            'site_enabled' => true,
            'pages' => [
                '' => [
                    'sections' => [
                        'blog-1' => ['fields' => ['title', 'items']],
                    ],
                ],
            ],
        ]);
        $media->create('image', '/uploads/library/a.webp', 'a.webp', 'image/webp', 10, '', MediaRepository::OWNER_CLIENT);
        $media->create('image', '/uploads/media/dev.webp', 'dev.webp', 'image/webp', 10, '', MediaRepository::OWNER_DEV);

        $rows = PageRowsBuilder::build($site, $pages);
        $stats = new DashboardStats($site, $pages, $media);
        $cards = $stats->build($rows);

        $labels = array_column($cards, 'label');
        $this->assertContains('Page', $labels);
        $this->assertContains('Articles', $labels);
        $this->assertContains('Média', $labels);

        $byLabel = [];
        foreach ($cards as $card) {
            $byLabel[$card['label']] = $card['value'];
        }
        $this->assertSame('1', $byLabel['Page']);
        $this->assertSame('3', $byLabel['Articles']);
        $this->assertSame('1', $byLabel['Média']);

        $html = $stats->renderCards($cards);
        $this->assertStringContainsString('Articles', $html);
        $this->assertStringNotContainsString('Publiée', $html);
        $this->assertStringNotContainsString('Brouillon', $html);
    }
}
