<?php

declare(strict_types=1);

namespace Capsule\Middleware;

use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;

interface HandlerInterface
{
    public function handle(Request $request): Response;
}
