<?php

declare(strict_types=1);

use App\Http\HealthController;

return [
    'GET /api/health' => [HealthController::class, 'health'],
];
