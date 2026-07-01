<?php

declare(strict_types=1);

namespace Capsule;

use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\Middleware\HandlerInterface;
use Capsule\Middleware\MiddlewareInterface;

final class Kernel implements HandlerInterface
{
    private HandlerInterface $pipeline;

    /**
     * @param list<MiddlewareInterface> $middlewares
     */
    public function __construct(array $middlewares, private readonly HandlerInterface $last)
    {
        $handler = $last;
        for ($i = count($middlewares) - 1; $i >= 0; $i--) {
            $mw = $middlewares[$i];
            $handler = new class ($mw, $handler) implements HandlerInterface {
                public function __construct(
                    private readonly MiddlewareInterface $mw,
                    private readonly HandlerInterface $next,
                ) {
                }

                public function handle(Request $request): Response
                {
                    return $this->mw->process($request, $this->next);
                }
            };
        }
        $this->pipeline = $handler;
    }

    public function handle(Request $request): Response
    {
        return $this->pipeline->handle($request);
    }
}
