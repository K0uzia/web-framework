<?php

declare(strict_types=1);

namespace Tests\Http\Dev;

use App\Http\Dev\MediaUploadException;
use App\Http\Dev\MediaUploader;
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
}
