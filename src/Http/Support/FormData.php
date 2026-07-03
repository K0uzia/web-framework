<?php

declare(strict_types=1);

namespace Capsule\Http\Support;

use Capsule\Http\Message\Request;

final class FormData
{
    /**
     * @return array<string, string>
     */
    public static function fromRequest(Request $request): array
    {
        if ($request->method === 'POST' && $_POST !== []) {
            return self::stringify($_POST);
        }

        if ($request->rawBody === null || trim($request->rawBody) === '') {
            return [];
        }

        $contentType = strtolower($request->headers['Content-Type'] ?? '');
        if (str_contains($contentType, 'application/json')) {
            $decoded = json_decode($request->rawBody, true);

            return is_array($decoded) ? self::stringify($decoded) : [];
        }

        parse_str($request->rawBody, $parsed);

        return self::stringify($parsed);
    }

    /**
     * @param array<mixed> $data
     *
     * @return array<string, string>
     */
    private static function stringify(array $data): array
    {
        $out = [];
        foreach ($data as $key => $value) {
            if (!is_string($key)) {
                continue;
            }
            if (is_scalar($value) || $value === null) {
                $out[$key] = (string) $value;
            }
        }

        return $out;
    }
}
