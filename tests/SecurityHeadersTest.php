<?php

declare(strict_types=1);

namespace Tests;

use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\Middleware\HandlerInterface;
use Capsule\Middleware\SecurityHeaders;
use PHPUnit\Framework\TestCase;

final class SecurityHeadersTest extends TestCase
{
    public function testContentSecurityPolicyAllowsVideoEmbeds(): void
    {
        $middleware = new SecurityHeaders(dev: false, https: false);
        $request = new Request('GET', '/', [], [], [], []);
        $response = $middleware->process($request, new class implements HandlerInterface {
            public function handle(Request $request): Response
            {
                return new Response(200);
            }
        });

        $csp = $response->getHeaderLine('Content-Security-Policy');

        $this->assertStringContainsString("frame-src 'self'", $csp);
        $this->assertStringContainsString('https://www.youtube-nocookie.com', $csp);
        $this->assertStringContainsString('strict-origin-when-cross-origin', $response->getHeaderLine('Referrer-Policy'));
    }
}
