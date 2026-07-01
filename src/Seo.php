<?php

declare(strict_types=1);

namespace Capsule;

final class Seo
{
    /**
     * @param array<string, mixed> $graph
     */
    public static function jsonLd(array $graph): string
    {
        return json_encode($graph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    public static function canonicalUrl(string $baseUrl, string $path): string
    {
        $base = rtrim($baseUrl, '/');
        if ($path === '' || $path === '/') {
            return $base . '/';
        }

        return $base . '/' . ltrim($path, '/');
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function apply(array $data, string $path, string $baseUrl): array
    {
        $title = self::scalar($data, 'title');
        $description = self::scalar($data, 'description');

        if (self::scalar($data, 'canonical') === '') {
            $data['canonical'] = self::canonicalUrl($baseUrl, $path);
        }

        if (isset($data['json_ld']) && is_array($data['json_ld'])) {
            $data['json_ld'] = self::jsonLd($data['json_ld']);
        } elseif (self::scalar($data, 'json_ld') === '') {
            $data['json_ld'] = self::jsonLd(self::buildGraphFromSchemaFields($data, $title, $description));
        }

        return self::stripSchemaFields($data);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private static function buildGraphFromSchemaFields(array $data, string $title, string $description): array
    {
        $type = self::scalar($data, 'schema_type');
        if ($type === '') {
            $type = 'WebPage';
        }

        $name = self::scalar($data, 'schema_name');
        if ($name === '') {
            $name = $title;
        }

        $graph = [
            '@context' => 'https://schema.org',
            '@type' => $type,
            'name' => $name,
        ];

        if ($description !== '') {
            $graph['description'] = $description;
        }

        $canonical = self::scalar($data, 'canonical');
        if ($canonical !== '') {
            $graph['url'] = $canonical;
        }

        foreach ($data as $key => $value) {
            if (!str_starts_with($key, 'schema_')) {
                continue;
            }
            if (in_array($key, ['schema_type', 'schema_name'], true)) {
                continue;
            }
            if (!is_scalar($value)) {
                continue;
            }

            $property = substr($key, strlen('schema_'));
            if ($property === '' || !preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $property)) {
                continue;
            }

            $graph[$property] = (string) $value;
        }

        return $graph;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private static function stripSchemaFields(array $data): array
    {
        foreach (array_keys($data) as $key) {
            if (str_starts_with($key, 'schema_')) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function scalar(array $data, string $key): string
    {
        $value = $data[$key] ?? '';

        return is_scalar($value) ? (string) $value : '';
    }
}
