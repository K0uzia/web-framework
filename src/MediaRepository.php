<?php

declare(strict_types=1);

namespace Capsule;

use PDO;

final class MediaRepository
{
    public const OWNER_DEV = 'dev';
    public const OWNER_CLIENT = 'client';

    public function __construct(private readonly PDO $pdo)
    {
        $this->ensureSchema();
    }

    /**
     * @return list<array{id: string, kind: string, url: string, filename: string, mime: string, size: int, label: string, owner: string, created_at: string}>
     */
    public function all(?string $kind = null, ?string $owner = null): array
    {
        $sql = 'SELECT id, kind, url, filename, mime, size, label, owner, created_at FROM media';
        $where = [];
        $params = [];
        if ($kind !== null) {
            $where[] = 'kind = :kind';
            $params['kind'] = $kind;
        }
        if ($owner !== null) {
            $where[] = 'owner = :owner';
            $params['owner'] = $owner;
        }
        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY created_at DESC';

        if ($params === []) {
            $stmt = $this->pdo->query($sql);

            return array_map($this->hydrate(...), $stmt ? ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : []);
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return array_map($this->hydrate(...), $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
    }

    /**
     * @return array{id: string, kind: string, url: string, filename: string, mime: string, size: int, label: string, owner: string, created_at: string}|null
     */
    public function findById(string $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, kind, url, filename, mime, size, label, owner, created_at FROM media WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $this->hydrate($row) : null;
    }

    /**
     * @return array{id: string, kind: string, url: string, filename: string, mime: string, size: int, label: string, owner: string, created_at: string}|null
     */
    public function findByUrl(string $url): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, kind, url, filename, mime, size, label, owner, created_at FROM media WHERE url = :url LIMIT 1');
        $stmt->execute(['url' => $url]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $this->hydrate($row) : null;
    }

    /**
     * @return array{id: string, kind: string, url: string, filename: string, mime: string, size: int, label: string, owner: string, created_at: string}
     */
    public function create(
        string $kind,
        string $url,
        string $filename,
        string $mime,
        int $size,
        string $label = '',
        string $owner = self::OWNER_DEV,
    ): array {
        $owner = $this->normalizeOwner($owner);
        $id = $kind . '-' . bin2hex(random_bytes(8));
        $stmt = $this->pdo->prepare(
            'INSERT INTO media (id, kind, url, filename, mime, size, label, owner) VALUES (:id, :kind, :url, :filename, :mime, :size, :label, :owner)',
        );
        $stmt->execute([
            'id' => $id,
            'kind' => $kind,
            'url' => $url,
            'filename' => $filename,
            'mime' => $mime,
            'size' => $size,
            'label' => $label,
            'owner' => $owner,
        ]);

        return $this->findById($id) ?? [
            'id' => $id,
            'kind' => $kind,
            'url' => $url,
            'filename' => $filename,
            'mime' => $mime,
            'size' => $size,
            'label' => $label,
            'owner' => $owner,
            'created_at' => '',
        ];
    }

    public function delete(string $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM media WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }

    /**
     * @return list<string>
     */
    public function urlsByKind(string $kind, ?string $owner = null): array
    {
        if ($owner === null) {
            $stmt = $this->pdo->prepare('SELECT url FROM media WHERE kind = :kind ORDER BY created_at DESC');
            $stmt->execute(['kind' => $kind]);
        } else {
            $stmt = $this->pdo->prepare('SELECT url FROM media WHERE kind = :kind AND owner = :owner ORDER BY created_at DESC');
            $stmt->execute(['kind' => $kind, 'owner' => $this->normalizeOwner($owner)]);
        }
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);

        return is_array($rows) ? array_values(array_map('strval', $rows)) : [];
    }

    public static function ownerFromUrl(string $url): string
    {
        if (str_starts_with($url, '/uploads/library/')) {
            return self::OWNER_CLIENT;
        }

        return self::OWNER_DEV;
    }

    private function ensureSchema(): void
    {
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS media (
                id TEXT PRIMARY KEY,
                kind TEXT NOT NULL CHECK (kind IN (\'image\', \'video\')),
                url TEXT NOT NULL UNIQUE,
                filename TEXT NOT NULL,
                mime TEXT NOT NULL DEFAULT \'\',
                size INTEGER NOT NULL DEFAULT 0,
                label TEXT NOT NULL DEFAULT \'\',
                owner TEXT NOT NULL DEFAULT \'dev\' CHECK (owner IN (\'dev\', \'client\')),
                created_at TEXT NOT NULL DEFAULT (datetime(\'now\'))
            )',
        );

        $cols = $this->pdo->query('PRAGMA table_info(media)')?->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $names = array_map(static fn (array $col): string => (string) ($col['name'] ?? ''), $cols);
        if (!in_array('owner', $names, true)) {
            $this->pdo->exec("ALTER TABLE media ADD COLUMN owner TEXT NOT NULL DEFAULT 'dev'");
            $this->pdo->exec("UPDATE media SET owner = 'client' WHERE url LIKE '/uploads/library/%'");
            $this->pdo->exec("UPDATE media SET owner = 'dev' WHERE owner IS NULL OR owner = ''");
        }

        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_media_kind ON media(kind)');
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_media_owner ON media(owner)');
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_media_created ON media(created_at)');
    }

    private function normalizeOwner(string $owner): string
    {
        return $owner === self::OWNER_CLIENT ? self::OWNER_CLIENT : self::OWNER_DEV;
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array{id: string, kind: string, url: string, filename: string, mime: string, size: int, label: string, owner: string, created_at: string}
     */
    private function hydrate(array $row): array
    {
        $url = (string) ($row['url'] ?? '');

        return [
            'id' => (string) ($row['id'] ?? ''),
            'kind' => (string) ($row['kind'] ?? ''),
            'url' => $url,
            'filename' => (string) ($row['filename'] ?? ''),
            'mime' => (string) ($row['mime'] ?? ''),
            'size' => (int) ($row['size'] ?? 0),
            'label' => (string) ($row['label'] ?? ''),
            'owner' => $this->normalizeOwner((string) ($row['owner'] ?? self::ownerFromUrl($url))),
            'created_at' => (string) ($row['created_at'] ?? ''),
        ];
    }
}
