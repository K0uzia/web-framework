<?php

declare(strict_types=1);

namespace Tests\Http\Dev;

use App\Http\Dev\FontUploadException;
use App\Http\Dev\FontUploader;
use PHPUnit\Framework\TestCase;

final class FontUploaderTest extends TestCase
{
    private string $dir;
    private FontUploader $uploader;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/capsule-font-test-' . bin2hex(random_bytes(4));
        $this->uploader = new FontUploader($this->dir);
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

    private function fakeUpload(string $filename): array
    {
        $tmp = tempnam(sys_get_temp_dir(), 'font');
        file_put_contents((string) $tmp, 'binary-font-bytes');

        return ['name' => $filename, 'type' => 'font/ttf', 'tmp_name' => $tmp, 'error' => UPLOAD_ERR_OK, 'size' => 18];
    }

    public function testStoreRejectsMissingFile(): void
    {
        $this->expectException(FontUploadException::class);
        $this->uploader->store(['error' => UPLOAD_ERR_NO_FILE]);
    }

    public function testStoreRejectsUploadError(): void
    {
        $this->expectException(FontUploadException::class);
        $file = $this->fakeUpload('brand.ttf');
        $file['error'] = UPLOAD_ERR_PARTIAL;
        $this->uploader->store($file);
    }

    public function testDeleteIgnoresUrlsOutsideManagedDirectory(): void
    {
        $this->uploader->delete('https://example.com/evil.woff2');
        $this->uploader->delete('/uploads/fonts/../../etc/passwd');
        $this->addToAssertionCount(1);
    }
}
