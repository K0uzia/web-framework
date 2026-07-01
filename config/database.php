<?php

declare(strict_types=1);

// Configuration PDO optionnelle. Sur disque reseau, SQLite bascule vers le dossier temporaire.
function capsule_sqlite_dsn(): string
{
    $root = dirname(__DIR__);
    $defaultPath = $root . '/data/database.sqlite';

    if (str_starts_with($root, '/mnt/')) {
        $hash = md5($root);
        $path = rtrim(sys_get_temp_dir(), '/') . "/capsule-db-{$hash}/database.sqlite";
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        return 'sqlite:' . $path;
    }

    return 'sqlite:' . $defaultPath;
}

return [
    'dsn'      => $_ENV['DB_DSN'] ?? capsule_sqlite_dsn(),
    'user'     => $_ENV['DB_USER'] ?? null,
    'password' => $_ENV['DB_PASSWORD'] ?? null,
    'options'  => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],
];
