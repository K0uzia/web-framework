<?php

declare(strict_types=1);

namespace Tests;

use Capsule\ProcessRunner;
use Capsule\VideoImportConfig;
use Capsule\YtDlpDownloader;
use Capsule\YouTubeUrlValidator;
use PHPUnit\Framework\TestCase;

final class YtDlpDownloaderTest extends TestCase
{
    public function testDetectsOutdatedVersion(): void
    {
        $downloader = $this->makeDownloader();

        $this->assertTrue($downloader->isVersionLikelyOutdated('2024.04.09'));
        $this->assertFalse($downloader->isVersionLikelyOutdated('2025.12.01'));
    }

    private function makeDownloader(): YtDlpDownloader
    {
        $config = new VideoImportConfig(
            importsRoot: sys_get_temp_dir(),
            publicBasePath: '/uploads/media/imports',
            ytDlpBin: 'yt-dlp',
            ffmpegBin: 'ffmpeg',
            maxFileBytes: 1,
            maxQueuePerOwner: 1,
            requireApproval: false,
            maxAttempts: 1,
            diskQuotaBytes: 1,
        );

        return new YtDlpDownloader(new ProcessRunner(), $config, new YouTubeUrlValidator());
    }
}
