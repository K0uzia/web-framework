<?php

declare(strict_types=1);

namespace Tests;

use Capsule\MediaRepository;
use Capsule\VideoImportCleaner;
use Capsule\VideoImportConfig;
use Capsule\VideoImportRepository;
use Capsule\VideoImportService;
use Capsule\YouTubeUrlValidator;
use PHPUnit\Framework\TestCase;

final class VideoImportServiceTest extends TestCase
{
    private VideoImportService $service;
    private string $importsRoot;

    protected function setUp(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__) . '/migrations/sqlite_init.sql') ?: '');
        $repo = new VideoImportRepository($pdo);
        $this->importsRoot = sys_get_temp_dir() . '/capsule-video-import-' . bin2hex(random_bytes(4));
        mkdir($this->importsRoot, 0775, true);

        $config = new VideoImportConfig(
            importsRoot: $this->importsRoot,
            publicBasePath: '/uploads/media/imports',
            ytDlpBin: 'yt-dlp',
            ffmpegBin: 'ffmpeg',
            maxFileBytes: 1024 * 1024,
            maxQueuePerOwner: 2,
            requireApproval: false,
            maxAttempts: 3,
            diskQuotaBytes: 10 * 1024 * 1024,
        );

        $media = new MediaRepository($pdo);
        $cleaner = new VideoImportCleaner($config, $media);

        $this->service = new VideoImportService($repo, $config, new YouTubeUrlValidator(), $cleaner);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->importsRoot)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->importsRoot, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST,
            );
            foreach ($files as $file) {
                if ($file->isDir()) {
                    rmdir($file->getPathname());
                } else {
                    unlink($file->getPathname());
                }
            }
            rmdir($this->importsRoot);
        }
    }

    public function testEnqueueYouTubeRequiresRights(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->enqueueYouTube('https://www.youtube.com/watch?v=dQw4w9WgXcQ', false);
    }

    public function testEnqueueYouTubeQueuesJob(): void
    {
        $job = $this->service->enqueueYouTube('https://www.youtube.com/watch?v=dQw4w9WgXcQ', true);
        $this->assertSame('queued', $job['status']);
        $this->assertSame('dQw4w9WgXcQ', $job['youtube_id']);
    }

    public function testEnqueueYouTubeRejectsInvalidUrl(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->enqueueYouTube('https://evil.test/foo', true);
    }

    public function testStatusPayload(): void
    {
        $job = $this->service->enqueueYouTube('https://youtu.be/dQw4w9WgXcQ', true);
        $status = $this->service->statusPayload((string) $job['id']);
        $this->assertSame('queued', $status['status']);
    }

    public function testRemoveQueuedJob(): void
    {
        $job = $this->service->enqueueYouTube('https://youtu.be/dQw4w9WgXcQ', true);
        $id = (string) $job['id'];
        $this->service->remove($id);
        $this->expectException(\InvalidArgumentException::class);
        $this->service->statusPayload($id);
    }
}
