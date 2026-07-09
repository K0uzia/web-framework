<?php

declare(strict_types=1);

namespace Tests;

use Capsule\VideoImportRepository;
use PHPUnit\Framework\TestCase;

final class VideoImportRepositoryTest extends TestCase
{
    private VideoImportRepository $repo;

    protected function setUp(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__) . '/migrations/sqlite_init.sql') ?: '');
        $this->repo = new VideoImportRepository($pdo);
    }

    public function testCreateAndFind(): void
    {
        $job = $this->repo->create([
            'source' => 'youtube',
            'source_url' => 'https://www.youtube.com/watch?v=abc12345',
            'youtube_id' => 'abc12345',
            'status' => 'queued',
            'rights_accepted' => true,
        ]);

        $found = $this->repo->findById($job['id']);
        $this->assertNotNull($found);
        $this->assertSame('queued', $found['status']);
        $this->assertSame('abc12345', $found['youtube_id']);
    }

    public function testClaimNextMovesToDownloading(): void
    {
        $job = $this->repo->create([
            'source' => 'youtube',
            'source_url' => 'https://www.youtube.com/watch?v=abc12345',
            'status' => 'queued',
            'approved' => 1,
        ]);

        $claimed = $this->repo->claimNext();
        $this->assertNotNull($claimed);
        $this->assertSame($job['id'], $claimed['id']);
        $this->assertSame('downloading', $claimed['status']);
    }
}
