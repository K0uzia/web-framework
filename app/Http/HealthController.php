<?php

declare(strict_types=1);

namespace App\Http;

use Capsule\Http\Factory\ResponseFactory;
use Capsule\Http\Message\Response;

final class HealthController
{
    public function __construct(private readonly ResponseFactory $responseFactory)
    {
    }

    public function health(\Capsule\Http\Message\Request $request): Response
    {
        return $this->responseFactory->json([
            'status' => 'ok',
            'service' => 'capsule-micro',
            'deploy' => 'base-path-v1',
            'path' => $request->path,
        ]);
    }
}
