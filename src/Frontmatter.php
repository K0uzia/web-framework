<?php

declare(strict_types=1);

namespace Capsule;

final class Frontmatter
{
    /**
     * @return array{meta: array<string, mixed>, body: string}
     */
    public static function parse(string $raw): array
    {
        $raw = ltrim($raw, "\xEF\xBB\xBF");
        if (!str_starts_with($raw, "---\n") && !str_starts_with($raw, "---\r\n")) {
            return ['meta' => [], 'body' => $raw];
        }

        $end = strpos($raw, "\n---", 4);
        if ($end === false) {
            return ['meta' => [], 'body' => $raw];
        }

        $yamlBlock = substr($raw, 4, $end - 4);
        $body = ltrim(substr($raw, $end + 4), "\r\n");

        return [
            'meta' => YamlData::parse($yamlBlock),
            'body' => $body,
        ];
    }
}
