<?php

declare(strict_types=1);

namespace Tests\Http\Dev;

use App\Http\Dev\MediaController;
use App\Http\Dev\MediaUploader;
use Capsule\DevDashboard;
use Capsule\Http\Factory\ResponseFactory;
use Capsule\Http\Message\Request;
use Capsule\SiteRepository;
use PHPUnit\Framework\TestCase;

final class MediaControllerTest extends TestCase
{
    private SiteRepository $site;
    private MediaController $controller;
    private string $uploadsDir;

    protected function setUp(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__, 3) . '/migrations/sqlite_init.sql') ?: '');

        $this->site = new SiteRepository($pdo);
        $ui = new DevDashboard(dirname(__DIR__, 3) . '/resources/dev', new ResponseFactory());
        $this->uploadsDir = sys_get_temp_dir() . '/capsule-media-controller-' . bin2hex(random_bytes(4));
        $this->controller = new MediaController($ui, $this->site, new MediaUploader($this->uploadsDir), new ResponseFactory());
    }

    public function testUploadWithUnknownFieldRedirects(): void
    {
        $response = $this->controller->upload($this->request('POST', '/dev/media/unknown/upload'), 'unknown');
        $this->assertSame(302, $response->getStatus());
    }

    public function testUploadWithoutFileReturnsErrorFragmentForHxRequest(): void
    {
        $response = $this->controller->upload($this->request('POST', '/dev/media/logo/upload', true), 'logo');
        $this->assertSame(200, $response->getStatus());
        $this->assertStringContainsString('dev-uploader__error', (string) $response->getBody());
        $this->assertStringContainsString('Aucun fichier', (string) $response->getBody());
    }

    public function testRemoveClearsStoredUrl(): void
    {
        $site = $this->site->getSite();
        $site['logo_url'] = '/uploads/site/logo-fake.png';
        $this->site->setSite($site);

        $response = $this->controller->remove($this->request('POST', '/dev/media/logo/remove', true), 'logo');

        $this->assertSame(200, $response->getStatus());
        $this->assertSame('', $this->site->getSite()['logo_url']);
    }

    public function testRemoveWithUnknownFieldRedirects(): void
    {
        $response = $this->controller->remove($this->request('POST', '/dev/media/unknown/remove'), 'unknown');
        $this->assertSame(302, $response->getStatus());
    }

    private function request(string $method, string $path, bool $hx = false): Request
    {
        return new Request(
            $method,
            $path,
            [],
            $hx ? ['HX-Request' => 'true'] : [],
            [],
            [],
        );
    }
}
