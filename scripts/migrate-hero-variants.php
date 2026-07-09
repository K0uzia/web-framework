#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Renomme les variantes hero dans SQLite (slugs séquentiels → slugs extension).
 *
 * Usage: php scripts/migrate-hero-variants.php [chemin/database.sqlite]
 */

require dirname(__DIR__) . '/src/Autoload.php';

/** @var array<string, string> */
const SEQUENTIAL_TO_EXTENSION = [
    'hero2' => 'hero3',
    'hero3' => 'hero7',
    'hero4' => 'hero12',
    'hero5' => 'hero34',
    'hero6' => 'hero45',
    'hero7' => 'hero47',
    'hero8' => 'hero67',
    'hero9' => 'hero78',
    'hero10' => 'hero115',
    'hero11' => 'hero195',
    'hero12' => 'hero206',
    'hero13' => 'hero243',
];

$dbPath = $argv[1] ?? dirname(__DIR__) . '/data/database.sqlite';
if (!is_file($dbPath)) {
    fwrite(STDERR, "Base introuvable : {$dbPath}\n");
    exit(1);
}

$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->query('SELECT slug, sections FROM pages');
$updated = 0;

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $sections = json_decode((string) ($row['sections'] ?? ''), true);
    if (!is_array($sections)) {
        continue;
    }

    $changed = false;
    foreach ($sections as &$section) {
        if (!is_array($section) || ($section['type'] ?? '') !== 'hero') {
            continue;
        }
        $variant = (string) ($section['variant'] ?? '');
        $normalized = SEQUENTIAL_TO_EXTENSION[$variant] ?? $variant;
        if ($normalized !== $variant) {
            $section['variant'] = $normalized;
            $changed = true;
        }
    }
    unset($section);

    if (!$changed) {
        continue;
    }

    $json = json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $update = $pdo->prepare('UPDATE pages SET sections = ?, updated_at = datetime(\'now\') WHERE slug = ?');
    $update->execute([$json, $row['slug']]);
    $updated++;
}

fwrite(STDOUT, "Pages mises à jour : {$updated}\n");
