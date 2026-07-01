<?php

declare(strict_types=1);

namespace Tests;

use Capsule\View;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(View::class)]
final class ViewTest extends TestCase
{
    private string $tmpDir;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/capsule-view-' . bin2hex(random_bytes(4));
        mkdir($this->tmpDir);
    }

    protected function tearDown(): void
    {
        @unlink($this->tmpDir . '/escape.php');
        @rmdir($this->tmpDir);
    }

    public function testEscapedInterpolation(): void
    {
        file_put_contents($this->tmpDir . '/escape.php', '<p>{{name}}</p>');
        $view = new View($this->tmpDir);

        $html = $view->render('escape.php', ['name' => '<script>alert(1)</script>']);

        $this->assertSame('<p>&lt;script&gt;alert(1)&lt;/script&gt;</p>', $html);
    }

    public function testRawInterpolation(): void
    {
        file_put_contents($this->tmpDir . '/escape.php', '<div>{{{body}}}</div>');
        $view = new View($this->tmpDir);

        $html = $view->render('escape.php', ['body' => '<strong>ok</strong>']);

        $this->assertSame('<div><strong>ok</strong></div>', $html);
    }
}
