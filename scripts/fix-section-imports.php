<?php

declare(strict_types=1);

$root = dirname(__DIR__) . '/src/Section';

/** @var list<string> */
$globalUses = [
    'SectionAssets' => 'Capsule\\SectionAssets',
    'SectionLayoutFamilies' => 'Capsule\\SectionLayoutFamilies',
    'FontAwesomeIcon' => 'Capsule\\FontAwesomeIcon',
    'MediaDisplaySettings' => 'Capsule\\MediaDisplaySettings',
    'View' => 'Capsule\\View',
    'Utf8' => 'Capsule\\Support\\Utf8',
    'SectionItemsTrait' => 'Capsule\\Section\\SectionItemsTrait',
    'SectionSocialIcons' => 'Capsule\\Section\\Support\\SectionSocialIcons',
    'SectionButtons' => 'Capsule\\Section\\Support\\SectionButtons',
];

foreach (glob($root . '/*/*.php') ?: [] as $file) {
    if (str_contains($file, '/Support/')) {
        continue;
    }
    $code = file_get_contents($file);
    if ($code === false) {
        continue;
    }

    $needed = [];
    foreach ($globalUses as $short => $fqcn) {
        if (preg_match('/\b' . preg_quote($short, '/') . '::/', $code) === 1
            || preg_match('/\buse ' . preg_quote($short, '/') . ';/', $code) === 1
            || preg_match('/\bextends ' . preg_quote($short, '/') . '\b/', $code) === 1
            || preg_match('/\b' . preg_quote($short, '/') . '\b/', $code) === 1 && $short === 'SectionItemsTrait') {
            if (!str_contains($code, 'use ' . $fqcn . ';') && !str_contains($code, 'use ' . $short . ';')) {
                $needed[$short] = $fqcn;
            }
        }
    }

    if ($needed === []) {
        continue;
    }

    $useLines = '';
    foreach ($needed as $fqcn) {
        $useLines .= "use {$fqcn};\n";
    }

    if (str_contains($code, "use Capsule\\Section\\AbstractSectionTypeHandler;")) {
        $code = str_replace(
            "use Capsule\\Section\\SectionEnrichContext;\n\n",
            "use Capsule\\Section\\SectionEnrichContext;\n{$useLines}\n",
            $code,
        );
    } else {
        $code = preg_replace(
            '/(namespace [^;]+;\n\n)/',
            "$1{$useLines}\n",
            $code,
            1,
        ) ?? $code;
    }

    file_put_contents($file, $code);
}

echo "Imports globaux corrigés.\n";
