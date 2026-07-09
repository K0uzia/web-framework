<?php

declare(strict_types=1);

namespace Tests;

use Capsule\MediaRepository;
use PHPUnit\Framework\TestCase;

final class MediaRepositoryTest extends TestCase
{
    public function testCreateAndListByKind(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $repo = new MediaRepository($pdo);

        $record = $repo->create('image', '/uploads/media/image-abc.webp', 'image-abc.webp', 'image/webp', 1200, 'Test');

        $this->assertSame('image', $record['kind']);
        $this->assertSame('/uploads/media/image-abc.webp', $record['url']);

        $images = $repo->all('image');
        $this->assertCount(1, $images);
        $this->assertSame($record['id'], $images[0]['id']);

        $this->assertTrue($repo->delete($record['id']));
        $this->assertSame([], $repo->all('image'));
    }
}
