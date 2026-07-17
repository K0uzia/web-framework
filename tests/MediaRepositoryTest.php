<?php

declare(strict_types=1);

namespace Tests;

use Capsule\MediaRepository;
use PHPUnit\Framework\TestCase;

final class MediaRepositoryTest extends TestCase
{
    public function testCreateAndListByKindAndOwner(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $repo = new MediaRepository($pdo);

        $dev = $repo->create('image', '/uploads/media/image-abc.webp', 'image-abc.webp', 'image/webp', 1200, 'Dev');
        $client = $repo->create(
            'image',
            '/uploads/library/client.webp',
            'client.webp',
            'image/webp',
            800,
            'Client',
            MediaRepository::OWNER_CLIENT,
        );

        $this->assertSame(MediaRepository::OWNER_DEV, $dev['owner']);
        $this->assertSame(MediaRepository::OWNER_CLIENT, $client['owner']);
        $this->assertCount(2, $repo->all('image'));
        $this->assertCount(1, $repo->all('image', MediaRepository::OWNER_DEV));
        $this->assertCount(1, $repo->all('image', MediaRepository::OWNER_CLIENT));
        $this->assertSame(['/uploads/library/client.webp'], $repo->urlsByKind('image', MediaRepository::OWNER_CLIENT));

        $this->assertTrue($repo->delete($dev['id']));
        $this->assertCount(0, $repo->all('image', MediaRepository::OWNER_DEV));
        $this->assertCount(1, $repo->all('image', MediaRepository::OWNER_CLIENT));
    }

    public function testOwnerFromUrl(): void
    {
        $this->assertSame(MediaRepository::OWNER_CLIENT, MediaRepository::ownerFromUrl('/uploads/library/a.webp'));
        $this->assertSame(MediaRepository::OWNER_DEV, MediaRepository::ownerFromUrl('/uploads/media/a.webp'));
    }
}
