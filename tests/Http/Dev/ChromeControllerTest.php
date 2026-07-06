<?php

declare(strict_types=1);

namespace Tests\Http\Dev;

use App\Http\Dev\ChromeController;
use Capsule\ChromeVariants;
use Capsule\DevDashboard;
use Capsule\Http\Factory\ResponseFactory;
use Capsule\Http\Message\Request;
use Capsule\Page;
use Capsule\PageRepository;
use Capsule\SiteRepository;
use PHPUnit\Framework\TestCase;

final class ChromeControllerTest extends TestCase
{
    private SiteRepository $site;
    private ChromeController $controller;

    protected function setUp(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__, 3) . '/migrations/sqlite_init.sql') ?: '');

        $pages = new PageRepository($pdo);
        $this->site = new SiteRepository($pdo);
        $ui = new DevDashboard(dirname(__DIR__, 3) . '/resources/dev', new ResponseFactory());
        $this->controller = new ChromeController($ui, $this->site, $pages);

        $pages->save(new Page('', 'Home', 'default', '', [], [], true, ''));
    }

    public function testCreateHeaderVariantMaterializesListAndKeepsActive(): void
    {
        $this->controller->create($this->post('/dev/chrome/header/create', 'variant_name=Landing'), 'header');

        $site = $this->site->getSite();
        $variants = ChromeVariants::headerVariants($site);
        $this->assertCount(2, $variants);
        $names = array_column($variants, 'name');
        $this->assertContains('Landing', $names);
        // La variante par défaut reste active tant qu'aucune activation explicite.
        $this->assertSame('default', ChromeVariants::activeHeaderId($site));
    }

    public function testUpdateHeaderVariantPersistsElementsAndZones(): void
    {
        $body = 'variant_id=default&variant_name=Principal'
            . '&brand_show_logo=1&brand_show_name=0&brand_show_tagline=0'
            . '&nav_visible=1&zone_brand=center&zone_nav=left&zone_cta=right&zone_login=right'
            . '&cta_enabled=1&cta_label=Essayer&cta_href=%2Fabout&cta_style=primary'
            . '&login_enabled=1&login_label=Connexion&login_href=%2Flogin&login_style=outline';
        $this->controller->update($this->post('/dev/chrome/header', $body), 'header');

        $site = $this->site->getSite();
        $header = ChromeVariants::resolveHeader($site);
        $this->assertSame('Principal', $header['name']);
        $this->assertFalse($header['brand']['show_name']);
        $this->assertSame('center', $header['layout']['brand']);
        $this->assertSame('left', $header['layout']['nav']);
        $this->assertTrue($header['cta']['enabled']);
        $this->assertSame('primary', $header['cta']['style']);
        $this->assertSame('outline', $header['login']['style']);
        $this->assertSame('Essayer', $header['cta']['label']);
        $this->assertTrue($header['login']['enabled']);
    }

    public function testActivateSwitchesActiveVariantAndShowsPartial(): void
    {
        $this->controller->create($this->post('/dev/chrome/footer/create', 'variant_name=Minimal'), 'footer');
        $site = $this->site->getSite();
        $variants = ChromeVariants::footerVariants($site);
        $newId = (string) $variants[array_key_last($variants)]['id'];

        $this->controller->activate($this->post('/dev/chrome/footer/' . $newId . '/activate', 'active=1'), 'footer', $newId);

        $site = $this->site->getSite();
        $this->assertSame($newId, ChromeVariants::activeFooterId($site));
        $this->assertTrue($site['partials']['footer']);
    }

    public function testActivateWithZeroHidesPartialButKeepsActiveVariant(): void
    {
        $this->controller->activate($this->post('/dev/chrome/header/default/activate', 'active=0'), 'header', 'default');

        $site = $this->site->getSite();
        $this->assertFalse($site['partials']['header']);
        $this->assertSame('default', ChromeVariants::activeHeaderId($site));

        // Réactivation : la variante redevient visible.
        $this->controller->activate($this->post('/dev/chrome/header/default/activate', 'active=1'), 'header', 'default');
        $site = $this->site->getSite();
        $this->assertTrue($site['partials']['header']);
    }

    public function testDeleteRefusesLastVariantAndReassignsActive(): void
    {
        // Refus : une seule variante.
        $this->controller->delete($this->post('/dev/chrome/header/default/delete', ''), 'header', 'default');
        $this->assertCount(1, ChromeVariants::headerVariants($this->site->getSite()));

        // Suppression de la variante active : l'active bascule sur la restante.
        $this->controller->create($this->post('/dev/chrome/header/create', 'variant_name=Alt'), 'header');
        $site = $this->site->getSite();
        $variants = ChromeVariants::headerVariants($site);
        $altId = (string) $variants[array_key_last($variants)]['id'];
        $this->controller->activate($this->post('/x', ''), 'header', $altId);
        $this->controller->delete($this->post('/x', ''), 'header', $altId);

        $site = $this->site->getSite();
        $this->assertCount(1, ChromeVariants::headerVariants($site));
        $this->assertSame('default', ChromeVariants::activeHeaderId($site));
    }

    public function testDuplicateCopiesConfiguration(): void
    {
        $body = 'variant_id=default&variant_name=Base&brand_show_logo=1&brand_show_name=1&brand_show_tagline=0'
            . '&nav_visible=0&zone_brand=left&zone_nav=right&zone_cta=right&zone_login=right'
            . '&cta_enabled=0&cta_label=&cta_href=&login_enabled=0&login_label=&login_href=';
        $this->controller->update($this->post('/dev/chrome/header', $body), 'header');
        $this->controller->duplicate($this->post('/x', ''), 'header', 'default');

        $variants = ChromeVariants::headerVariants($this->site->getSite());
        $this->assertCount(2, $variants);
        $copy = $variants[array_key_last($variants)];
        $this->assertSame('Base (copie)', $copy['name']);
        $this->assertFalse($copy['nav']['visible']);
        $this->assertNotSame('default', $copy['id']);
    }

    private function post(string $path, string $body): Request
    {
        return new Request(
            'POST',
            $path,
            [],
            ['Content-Type' => 'application/x-www-form-urlencoded'],
            [],
            [],
            rawBody: $body,
        );
    }
}
