<?php

declare(strict_types=1);

namespace Capsule;

final class YamlData
{
    /**
     * @return array<string, mixed>
     */
    public static function loadFile(string $path): array
    {
        $raw = file_get_contents($path);
        if ($raw === false) {
            throw new \RuntimeException("Cannot read YAML file: {$path}");
        }

        return self::parse($raw);
    }

    /**
     * @return array<string, mixed>
     */
    public static function parse(string $raw): array
    {
        $raw = ltrim($raw, "\xEF\xBB\xBF");
        $lines = preg_split('/\r\n|\n|\r/', $raw) ?: [];

        return self::parseLines($lines, 0)['data'];
    }

    /**
     * @param list<string> $lines
     * @return array{data: array<string, mixed>, next: int}
     */
    private static function parseLines(array $lines, int $start, int $parentIndent = -1): array
    {
        $data = [];
        $i = $start;
        $count = count($lines);

        while ($i < $count) {
            $line = rtrim($lines[$i], "\r");
            $trimmed = trim($line);

            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                $i++;
                continue;
            }

            $indent = strlen($line) - strlen(ltrim($line, ' '));
            if ($parentIndent >= 0 && $indent <= $parentIndent) {
                break;
            }

            if (!preg_match('/^([a-zA-Z_][a-zA-Z0-9_-]*):\s*(.*)$/', $trimmed, $m)) {
                $i++;
                continue;
            }

            $key = $m[1];
            $value = $m[2];

            if ($value === '') {
                $child = self::parseLines($lines, $i + 1, $indent);
                $data[$key] = $child['data'];
                $i = $child['next'];
                continue;
            }

            $data[$key] = self::parseScalar($value);
            $i++;
        }

        return ['data' => $data, 'next' => $i];
    }

    private static function parseScalar(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"'))
            || (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            return substr($value, 1, -1);
        }

        return $value;
    }

    public static function siblingDataFile(string $templateFile): ?string
    {
        $dir = dirname($templateFile);
        $base = pathinfo($templateFile, PATHINFO_FILENAME);

        foreach (['yaml', 'yml'] as $ext) {
            $candidate = $dir . DIRECTORY_SEPARATOR . $base . '.' . $ext;
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
