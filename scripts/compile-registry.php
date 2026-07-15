<?php

declare(strict_types=1);

/**
 * Compile registry.yaml : default_variant par type, style_fields partagés.
 *
 * Usage: php scripts/compile-registry.php
 */

require dirname(__DIR__) . '/vendor/autoload.php';

use Capsule\YamlData;

$root = dirname(__DIR__);
$registryPath = $root . '/resources/sections/registry.yaml';
$sharedStylePath = $root . '/resources/sections/_shared/style-fields.yaml';

/** @var array<string, string> */
const DEFAULT_VARIANTS = [
    'hero' => 'hero3',
    'features' => 'feature3',
    'integrations' => 'integration3',
    'pricing' => 'pricing2',
    'rate-card' => 'rate-card2',
    'contact' => 'contact2',
    'testimonials' => 'testimonial4',
    'gallery' => 'gallery4',
    'blog' => 'blog7',
    'changelog' => 'changelog1',
    'process' => 'process1',
    'list' => 'list2',
    'industry' => 'industries1',
    'download' => 'download1',
    'team' => 'team1',
    'projects' => 'projects5',
    'timeline' => 'timeline3',
];

$registry = YamlData::loadFile($registryPath);
$sharedStyle = YamlData::loadFile($sharedStylePath);

foreach ($registry as $type => &$def) {
    if (!is_array($def)) {
        continue;
    }

    $variants = is_array($def['variants'] ?? null) ? $def['variants'] : [];
    $variantKeys = array_keys($variants);

    if (!isset($def['default_variant']) || !is_string($def['default_variant'])) {
        $def['default_variant'] = DEFAULT_VARIANTS[$type] ?? ($variantKeys[0] ?? 'default');
    }

    $typeStyle = is_array($def['style_fields'] ?? null) ? $def['style_fields'] : [];
    $def['style_fields'] = mergeStyleFields($sharedStyle, $typeStyle);
}
unset($def);

file_put_contents($registryPath, dumpRegistry($registry));
fwrite(STDOUT, "Registry compilé : {$registryPath}\n");

/**
 * @param array<string, mixed> $shared
 * @param array<string, mixed> $typeSpecific
 *
 * @return array<string, mixed>
 */
function mergeStyleFields(array $shared, array $typeSpecific): array
{
    $merged = $shared;
    foreach ($typeSpecific as $key => $field) {
        $merged[$key] = $field;
    }

    return $merged;
}

/**
 * @param array<string, mixed> $data
 */
function dumpRegistry(array $data): string
{
    $lines = [];
    foreach ($data as $type => $def) {
        dumpKey($lines, (string) $type, $def, 0);
    }

    return implode("\n", $lines) . "\n";
}

/**
 * @param list<string> $lines
 * @param mixed $value
 */
function dumpKey(array &$lines, string $key, $value, int $indent): void
{
    $pad = str_repeat(' ', $indent);
    if (!is_array($value)) {
        $lines[] = $pad . $key . ': ' . dumpScalar($value);

        return;
    }
    if ($value === []) {
        $lines[] = $pad . $key . ': []';

        return;
    }
    if (isListArray($value)) {
        $lines[] = $pad . $key . ': ' . dumpInlineList($value);

        return;
    }
    $lines[] = $pad . $key . ':';
    foreach ($value as $childKey => $childValue) {
        dumpKey($lines, (string) $childKey, $childValue, $indent + 2);
    }
}

/**
 * @param array<int, mixed> $list
 */
function dumpInlineList(array $list): string
{
    $parts = [];
    foreach ($list as $item) {
        $parts[] = dumpScalar($item);
    }

    return '[' . implode(', ', $parts) . ']';
}

function dumpScalar(mixed $value): string
{
    if ($value === null) {
        return 'null';
    }
    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }
    if (is_int($value) || is_float($value)) {
        return (string) $value;
    }
    if (!is_string($value)) {
        return '""';
    }
    if ($value === '' || preg_match('/[:#\\[\\]{}&,\\*\\?]|^\\s|^-$/', $value)) {
        return "'" . str_replace("'", "''", $value) . "'";
    }

    return $value;
}

/**
 * @param array<mixed> $array
 */
function isListArray(array $array): bool
{
    if ($array === []) {
        return true;
    }

    return array_keys($array) === range(0, count($array) - 1);
}
