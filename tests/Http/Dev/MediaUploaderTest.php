<?php

declare(strict_types=1);

namespace Tests\Http\Dev;

use App\Http\Dev\LibraryMediaUploader;
use App\Http\Dev\MediaUploadException;
use App\Http\Dev\MediaUploader;
use Capsule\ProcessRunner;
use PHPUnit\Framework\TestCase;

final class MediaUploaderTest extends TestCase
{
    private string $dir;
    private MediaUploader $uploader;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/capsule-media-test-' . bin2hex(random_bytes(4));
        $this->uploader = new MediaUploader($this->dir);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->dir)) {
            foreach (glob($this->dir . '/*') ?: [] as $file) {
                @unlink($file);
            }
            @rmdir($this->dir);
        }
    }

    private function fakePngUpload(): array
    {
        // 1x1 PNG rouge minimal.
        $bytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');
        $tmp = tempnam(sys_get_temp_dir(), 'upl');
        file_put_contents($tmp, $bytes);

        return ['name' => 'logo.png', 'type' => 'image/png', 'tmp_name' => $tmp, 'error' => UPLOAD_ERR_OK, 'size' => strlen($bytes)];
    }

    public function testStoreRejectsUnknownField(): void
    {
        $this->expectException(MediaUploadException::class);
        $this->uploader->store('unknown', $this->fakePngUpload());
    }

    public function testStoreRejectsUploadError(): void
    {
        $this->expectException(MediaUploadException::class);
        $file = $this->fakePngUpload();
        $file['error'] = UPLOAD_ERR_PARTIAL;
        $this->uploader->store('logo', $file);
    }

    public function testStoreRejectsOversizedFile(): void
    {
        $this->expectException(MediaUploadException::class);
        $file = $this->fakePngUpload();
        $file['size'] = 10 * 1024 * 1024;
        $this->uploader->store('logo', $file);
    }

    public function testAcceptAttributeListsAllowedMimeTypesForField(): void
    {
        $this->assertStringContainsString('image/png', $this->uploader->acceptAttribute('logo'));
        $this->assertStringContainsString('image/x-icon', $this->uploader->acceptAttribute('favicon'));
    }

    public function testDeleteIgnoresUrlsOutsideManagedDirectory(): void
    {
        // Ne doit pas lever d'exception ni tenter de supprimer un chemin arbitraire.
        $this->uploader->delete('https://example.com/evil.png');
        $this->uploader->delete('/uploads/site/../../etc/passwd');
        $this->addToAssertionCount(1);
    }

    public function testWebpSupportAvailableViaGdOrFfmpeg(): void
    {
        $gd = function_exists('imagewebp') && function_exists('imagecreatefromstring');
        $ffmpeg = (new ProcessRunner())->isExecutable('ffmpeg');
        if (!$gd && !$ffmpeg) {
            $this->markTestSkipped('Ni GD WebP ni ffmpeg disponibles.');
        }

        $this->assertTrue($this->uploader->webpSupportAvailable());
    }

    public function testConvertToWebpProducesWebpFile(): void
    {
        if (!$this->uploader->webpSupportAvailable()) {
            $this->markTestSkipped('Ni GD WebP ni ffmpeg disponibles.');
        }

        $source = $this->dir . '/source.png';
        $destination = $this->dir . '/out.webp';
        mkdir($this->dir, 0775, true);

        $runner = new ProcessRunner();
        if ($runner->isExecutable('ffmpeg')) {
            $result = $runner->run([
                'ffmpeg', '-y', '-f', 'lavfi', '-i', 'color=c=red:s=32x32', '-frames:v', '1', $source,
            ], null, 60);
            $this->assertTrue($result->successful(), 'Impossible de générer une PNG de test via ffmpeg.');
        } elseif (function_exists('imagecreatetruecolor') && function_exists('imagepng')) {
            $img = imagecreatetruecolor(32, 32);
            $red = imagecolorallocate($img, 255, 0, 0);
            imagefilledrectangle($img, 0, 0, 31, 31, $red);
            imagepng($img, $source);
            imagedestroy($img);
        } else {
            $this->markTestSkipped('Impossible de générer une image de test.');
        }

        $method = new \ReflectionMethod(MediaUploader::class, 'convertToWebp');
        $method->invoke($this->uploader, $source, $destination);

        $this->assertFileExists($destination);
        $this->assertGreaterThan(0, filesize($destination));
        $this->assertSame('image/webp', (new \finfo(FILEINFO_MIME_TYPE))->file($destination));
    }

    public function testStoredFileMetaReflectsConvertedWebp(): void
    {
        mkdir($this->dir, 0775, true);
        $path = $this->dir . '/image-abc.webp';
        file_put_contents($path, 'webp-bytes');

        $uploader = new LibraryMediaUploader($this->dir);
        $meta = $uploader->storedFileMeta('/uploads/media/image-abc.webp', 'image/png', 999);

        $this->assertSame('image/webp', $meta['mime']);
        $this->assertSame(10, $meta['size']);
    }
}
