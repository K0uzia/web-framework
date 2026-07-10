<?php

declare(strict_types=1);

namespace Tests;

use Capsule\Http\Factory\ResponseFactory;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\Middleware\HandlerInterface;
use Capsule\Middleware\StaticAssetMiddleware;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StaticAssetMiddleware::class)]
final class StaticAssetMiddlewareTest extends TestCase
{
    private string $publicDir;
    private bool $nextCalled = false;

    protected function setUp(): void
    {
        $this->publicDir = sys_get_temp_dir() . '/capsule-static-' . bin2hex(random_bytes(4));
        mkdir($this->publicDir . '/assets/css', 0755, true);
        file_put_contents($this->publicDir . '/assets/css/base.css', 'body{color:red}');
    }

    protected function tearDown(): void
    {
        @unlink($this->publicDir . '/assets/css/base.css');
        @rmdir($this->publicDir . '/assets/css');
        @rmdir($this->publicDir . '/assets');
        @rmdir($this->publicDir);
    }

    public function testServesExistingAsset(): void
    {
        $middleware = new StaticAssetMiddleware($this->publicDir, new ResponseFactory());
        $request = new Request('GET', '/assets/css/base.css', [], [], [], []);
        $test = $this;
        $next = new class ($test) implements HandlerInterface {
            public function __construct(private StaticAssetMiddlewareTest $test)
            {
            }

            public function handle(Request $request): Response
            {
                $this->test->nextCalled = true;

                return new Response(404);
            }
        };

        $response = $middleware->process($request, $next);

        $this->assertFalse($this->nextCalled);
        $this->assertSame('body{color:red}', $response->getBody());
        $this->assertStringContainsString('text/css', $response->getHeaderLine('Content-Type'));
    }

    public function testPassesUnknownAssetToNextHandler(): void
    {
        $middleware = new StaticAssetMiddleware($this->publicDir, new ResponseFactory());
        $request = new Request('GET', '/assets/css/missing.css', [], [], [], []);
        $next = new class implements HandlerInterface {
            public function handle(Request $request): Response
            {
                return new Response(404, 'not-found');
            }
        };

        $response = $middleware->process($request, $next);

        $this->assertSame('not-found', $response->getBody());
    }

    public function testIgnoresNonAssetPaths(): void
    {
        $middleware = new StaticAssetMiddleware($this->publicDir, new ResponseFactory());
        $request = new Request('GET', '/dev', [], [], [], []);
        $next = new class implements HandlerInterface {
            public function handle(Request $request): Response
            {
                return new Response(200, 'dev');
            }
        };

        $response = $middleware->process($request, $next);

        $this->assertSame('dev', $response->getBody());
    }
}
