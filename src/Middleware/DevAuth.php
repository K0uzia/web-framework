<?php

declare(strict_types=1);

namespace Capsule\Middleware;

use Capsule\Http\Factory\ResponseFactory;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;

final class DevAuth implements MiddlewareInterface
{
    public function __construct(
        private readonly ResponseFactory $responses,
        private readonly string $devPassword,
        private readonly bool $isDev,
    ) {
    }

    public function process(Request $request, HandlerInterface $next): Response
    {
        $path = $request->path === '' ? '/' : $request->path;
        if (!str_starts_with($path, '/dev')) {
            return $next->handle($request);
        }

        $publicPaths = ['/dev', '/dev/login'];
        if (in_array($path, $publicPaths, true) && $request->method === 'GET') {
            return $next->handle($request);
        }
        if ($path === '/dev/login' && $request->method === 'POST') {
            return $next->handle($request);
        }

        if ($this->devPassword === '' && $this->isDev) {
            return $next->handle($request);
        }

        if (($request->cookies['capsule_dev'] ?? '') === '1') {
            return $next->handle($request);
        }

        return $this->responses->redirect('/dev');
    }
}
