<?php

declare(strict_types=1);

namespace Capsule\Middleware;

use Capsule\Http\Exception\HttpException;
use Capsule\Http\Factory\ResponseFactory;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;

final class ErrorBoundary implements MiddlewareInterface
{
    public function __construct(
        private readonly ResponseFactory $res,
        private readonly bool $debug = false,
        private readonly ?string $appName = null,
    ) {
    }

    public function process(Request $request, HandlerInterface $next): Response
    {
        $reqId = self::requestId();

        try {
            $resp = $next->handle($request);

            return $resp->withHeader('X-Request-Id', $reqId);
        } catch (HttpException $e) {
            $status = $e->status;
            $message = $e->getMessage() !== '' ? $e->getMessage() : ($status >= 500 ? 'Server Error' : 'HTTP Error');

            $payload = $this->basePayload($request, $reqId, $status, $message);
            if ($this->debug) {
                $payload['debug'] = $this->debugBlock($e);
            }

            $resp = $this->res->json($payload, $status)->withHeader('X-Request-Id', $reqId);

            foreach ($e->headers as $name => $values) {
                foreach ((array) $values as $value) {
                    $resp = $resp->withAddedHeader($name, (string) $value);
                }
            }

            return $resp;
        } catch (\Throwable $e) {
            $payload = $this->basePayload($request, $reqId, 500, 'Server Error');
            if ($this->debug) {
                $payload['debug'] = $this->debugBlock($e);
            }

            return $this->res->json($payload, 500)
                ->withHeader('X-Request-Id', $reqId);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function basePayload(Request $r, string $reqId, int $status, string $message): array
    {
        $base = [
            'requestId' => $reqId,
            'status' => $status,
            'error' => [
                'type' => $this->statusToType($status),
                'message' => $message,
            ],
            'request' => [
                'method' => $r->method,
                'path' => $r->path,
            ],
        ];

        if ($this->appName !== null && $this->appName !== '') {
            $base['app'] = $this->appName;
        }

        return $base;
    }

    private function statusToType(int $status): string
    {
        return match (true) {
            $status === 404 => 'not_found',
            $status === 405 => 'method_not_allowed',
            $status >= 500 => 'server_error',
            default => 'client_error',
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function debugBlock(\Throwable $e): array
    {
        return [
            'exception' => $e::class,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ];
    }

    private static function requestId(): string
    {
        return bin2hex(random_bytes(8));
    }
}
