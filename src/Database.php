<?php

declare(strict_types=1);

namespace Capsule;

use PDO;

final class Database
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public static function fromConfig(): self
    {
        /** @var array{dsn:string,user:?string,password:?string,options:array<int,mixed>} $config */
        $config = require dirname(__DIR__) . '/config/database.php';

        $pdo = new PDO(
            $config['dsn'],
            $config['user'] ?? null,
            $config['password'] ?? null,
            $config['options'],
        );
        self::ensureSchema($pdo);

        return new self($pdo);
    }

    private static function ensureSchema(PDO $pdo): void
    {
        $stmt = $pdo->query("SELECT 1 FROM sqlite_master WHERE type = 'table' AND name = 'pages' LIMIT 1");
        if ($stmt !== false && $stmt->fetch() !== false) {
            return;
        }

        $root = dirname(__DIR__);
        $migration = file_get_contents($root . '/migrations/sqlite_init.sql');
        if ($migration !== false && $migration !== '') {
            $pdo->exec($migration);
        }

        $seedPath = $root . '/migrations/seed_default_site.sql';
        if (is_file($seedPath)) {
            $seed = file_get_contents($seedPath);
            if ($seed !== false && $seed !== '') {
                $pdo->exec($seed);
            }
        }
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }
}
