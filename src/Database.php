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

        return new self($pdo);
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }
}
