<?php

declare(strict_types=1);

namespace Capsule\Middleware;

use Capsule\Http\Factory\ResponseFactory;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;

final class ClientAuth implements MiddlewareInterface
{
    public function __construct(
        private readonly ResponseFactory $responses,
        private readonly string $clientPassword,
        private readonly bool $isDev,
    ) {
    }

    public function process(Request $request, HandlerInterface $next): Response
    {
        $path = $request->path === '' ? '/' : $request->path;
        if (!str_starts_with($path, '/admin')) {
            return $next->handle($request);
        }

        $publicGet = ['/admin', '/admin/login'];
        if (in_array($path, $publicGet, true) && $request->method === 'GET') {
            return $next->handle($request);
        }
        if ($path === '/admin/login' && $request->method === 'POST') {
            return $next->handle($request);
        }

        if ($this->clientPassword === '' && $this->isDev) {
            return $next->handle($request);
        }

        if (($request->cookies['capsule_client'] ?? '') === '1') {
            return $next->handle($request);
        }

        return $this->responses->redirect('/admin');
    }
}
