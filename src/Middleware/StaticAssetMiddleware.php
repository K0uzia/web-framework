<?php

declare(strict_types=1);

namespace Capsule\Middleware;

use Capsule\Http\Factory\ResponseFactory;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;

/**
 * Sert /assets/* et /uploads/* depuis public/ quand Apache ne réécrit pas (FTP / mutualisé).
 */
final class StaticAssetMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly string $publicDir,
        private readonly ResponseFactory $responses,
    ) {
    }

    public function process(Request $request, HandlerInterface $next): Response
    {
        $method = strtoupper($request->method);
        if ($method !== 'GET' && $method !== 'HEAD') {
            return $next->handle($request);
        }

        $path = $request->path === '' ? '/' : $request->path;
        if (!preg_match('#^/(assets|uploads)/#', $path)) {
            return $next->handle($request);
        }

        $realPublic = realpath($this->publicDir);
        $realFile = realpath($this->publicDir . $path);
        if ($realPublic === false
            || $realFile === false
            || !is_file($realFile)
            || !str_starts_with($realFile, $realPublic . DIRECTORY_SEPARATOR)
            || preg_match('/\.php$/i', $realFile)) {
            return $next->handle($request);
        }

        $content = file_get_contents($realFile);
        if ($content === false) {
            return $next->handle($request);
        }

        $body = $method === 'HEAD' ? '' : $content;

        return $this->responses->createResponse(200, $body)
            ->withHeader('Content-Type', $this->mimeType($realFile))
            ->withHeader('Cache-Control', 'public, max-age=31536000, immutable')
            ->withHeader('X-Content-Type-Options', 'nosniff');
    }

    private function mimeType(string $file): string
    {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        return match ($ext) {
            'css' => 'text/css; charset=utf-8',
            'js' => 'text/javascript; charset=utf-8',
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'ico' => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'json' => 'application/json; charset=utf-8',
            default => 'application/octet-stream',
        };
    }
}
