<?php

declare(strict_types=1);

namespace Tests;

use Capsule\DevDashboard;
use Capsule\Http\Factory\ResponseFactory;
use Capsule\SiteRepository;
use PHPUnit\Framework\TestCase;

final class DevDashboardTest extends TestCase
{
    public function testRenderInjectsThemeCssAndBindings(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__) . '/migrations/sqlite_init.sql') ?: '');

        $site = new SiteRepository($pdo);
        $theme = $site->getTheme();
        $theme['colors']['success'] = '#112233';
        $site->setTheme($theme);

        $ui = new DevDashboard(dirname(__DIR__) . '/resources/dev', new ResponseFactory(), $site);
        $body = (string) $ui->render('overview.html', ['title' => 'Test', 'flash' => ''])->getBody();

        $this->assertStringContainsString('--color-success: #112233', $body);
        $this->assertStringContainsString('href="/assets/css/dev-theme-bindings.css"', $body);
        $bindingsPos = strpos($body, 'dev-theme-bindings.css');
        $themePos = strpos($body, '--color-success: #112233');
        $this->assertNotFalse($bindingsPos);
        $this->assertNotFalse($themePos);
        $this->assertLessThan($bindingsPos, $themePos);
    }
}
