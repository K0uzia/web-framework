<?php

declare(strict_types=1);

namespace App\Http\Dev;

use Capsule\Http\Message\Request;

trait DevHx
{
    private function isHx(Request $request): bool
    {
        foreach ($request->headers as $name => $value) {
            if (strcasecmp($name, 'HX-Request') === 0) {
                return strtolower($value) === 'true';
            }
        }

        return false;
    }
}
