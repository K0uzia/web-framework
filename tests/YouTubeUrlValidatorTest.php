<?php

declare(strict_types=1);

namespace Tests;

use Capsule\YouTubeUrlValidator;
use PHPUnit\Framework\TestCase;

final class YouTubeUrlValidatorTest extends TestCase
{
    private YouTubeUrlValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new YouTubeUrlValidator();
    }

    public function testAcceptsWatchUrl(): void
    {
        $id = $this->validator->extractVideoId('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

        $this->assertSame('dQw4w9WgXcQ', $id);
        $this->assertTrue($this->validator->isAllowedUrl('https://www.youtube.com/watch?v=dQw4w9WgXcQ'));
    }

    public function testAcceptsShortUrl(): void
    {
        $this->assertSame('dQw4w9WgXcQ', $this->validator->extractVideoId('https://youtu.be/dQw4w9WgXcQ'));
    }

    public function testRejectsInjectionInUrl(): void
    {
        $this->assertNull($this->validator->extractVideoId('https://evil.test/watch?v=dQw4w9WgXcQ;rm -rf /'));
        $this->assertNull($this->validator->extractVideoId('https://www.youtube.com/watch?v=$(whoami)'));
    }

    public function testRejectsUnknownHost(): void
    {
        $this->assertFalse($this->validator->isAllowedUrl('https://example.com/video.mp4'));
    }
}
