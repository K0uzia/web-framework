<?php

declare(strict_types=1);

namespace Capsule\Middleware;

use Capsule\BasePath;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;

final class BasePathMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly BasePath $basePath)
    {
    }

    public function process(Request $request, HandlerInterface $next): Response
    {
        $response = $next->handle($request);

        if ($this->basePath->isEmpty()) {
            return $response;
        }

        $location = $response->getHeaderLine('Location');
        if ($location !== '' && str_starts_with($location, '/') && !str_starts_with($location, $this->basePath->value() . '/')) {
            $response = $response->withHeader('Location', $this->basePath->url($location));
        }

        $body = $response->getBody();
        if (!is_string($body) || $body === '') {
            return $response;
        }

        $contentType = $response->getHeaderLine('Content-Type');
        if ($contentType !== '' && !str_contains(strtolower($contentType), 'text/html')) {
            return $response;
        }

        return $response->withBody($this->basePath->rewriteHtml($body));
    }
}
