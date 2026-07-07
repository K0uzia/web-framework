<?php

declare(strict_types=1);

namespace Capsule\Middleware;

use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;

final class SecurityHeaders implements MiddlewareInterface
{
    private const FRAME_EMBED_SRC = 'https://www.youtube.com https://www.youtube-nocookie.com https://player.vimeo.com';

    public function __construct(
        private readonly bool $dev = true,
        private readonly bool $https = false,
    ) {
    }

    public function process(Request $request, HandlerInterface $next): Response
    {
        $res = $next->handle($request);

        $path = $request->path === '' ? '/' : $request->path;
        $isDev = str_starts_with($path, '/dev');

        $csp = "default-src 'self'; "
            . "style-src 'self' 'unsafe-inline'; "
            . "script-src 'self'; "
            . "img-src 'self' data:; "
            . "font-src 'self'; "
            . "media-src 'self' blob:; "
            . "connect-src 'self'; "
            . "frame-src 'self' " . self::FRAME_EMBED_SRC . '; '
            . "form-action 'self'; "
            . "base-uri 'self'; "
            . ($isDev ? "frame-ancestors 'self';" : "frame-ancestors 'none';");

        if (!$res->hasHeader('Content-Security-Policy')) {
            $res = $res->withHeader('Content-Security-Policy', $csp);
        }
        if (!$res->hasHeader('X-Content-Type-Options')) {
            $res = $res->withHeader('X-Content-Type-Options', 'nosniff');
        }
        if (!$res->hasHeader('Referrer-Policy')) {
            $res = $res->withHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        }
        if (!$res->hasHeader('X-Frame-Options') && !$isDev) {
            $res = $res->withHeader('X-Frame-Options', 'DENY');
        }

        if (!$this->dev && $this->https && !$res->hasHeader('Strict-Transport-Security')) {
            $res = $res->withHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        return $res;
    }
}
