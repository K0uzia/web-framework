<?php

declare(strict_types=1);

namespace Capsule;

use PDO;

final class MediaRepository
{
    public function __construct(private readonly PDO $pdo)
    {
        $this->ensureSchema();
    }

    /**
     * @return list<array{id: string, kind: string, url: string, filename: string, mime: string, size: int, label: string, created_at: string}>
     */
    public function all(?string $kind = null): array
    {
        if ($kind !== null) {
            $stmt = $this->pdo->prepare('SELECT id, kind, url, filename, mime, size, label, created_at FROM media WHERE kind = :kind ORDER BY created_at DESC');
            $stmt->execute(['kind' => $kind]);

            return array_map($this->hydrate(...), $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
        }

        $stmt = $this->pdo->query('SELECT id, kind, url, filename, mime, size, label, created_at FROM media ORDER BY created_at DESC');

        return array_map($this->hydrate(...), $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
    }

    public function findById(string $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, kind, url, filename, mime, size, label, created_at FROM media WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $this->hydrate($row) : null;
    }

    public function findByUrl(string $url): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, kind, url, filename, mime, size, label, created_at FROM media WHERE url = :url LIMIT 1');
        $stmt->execute(['url' => $url]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $this->hydrate($row) : null;
    }

    /**
     * @return array{id: string, kind: string, url: string, filename: string, mime: string, size: int, label: string, created_at: string}
     */
    public function create(string $kind, string $url, string $filename, string $mime, int $size, string $label = ''): array
    {
        $id = $kind . '-' . bin2hex(random_bytes(8));
        $stmt = $this->pdo->prepare(
            'INSERT INTO media (id, kind, url, filename, mime, size, label) VALUES (:id, :kind, :url, :filename, :mime, :size, :label)',
        );
        $stmt->execute([
            'id' => $id,
            'kind' => $kind,
            'url' => $url,
            'filename' => $filename,
            'mime' => $mime,
            'size' => $size,
            'label' => $label,
        ]);

        return $this->findById($id) ?? [
            'id' => $id,
            'kind' => $kind,
            'url' => $url,
            'filename' => $filename,
            'mime' => $mime,
            'size' => $size,
            'label' => $label,
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
    public function urlsByKind(string $kind): array
    {
        $stmt = $this->pdo->prepare('SELECT url FROM media WHERE kind = :kind ORDER BY created_at DESC');
        $stmt->execute(['kind' => $kind]);
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);

        return is_array($rows) ? array_values(array_map('strval', $rows)) : [];
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
                created_at TEXT NOT NULL DEFAULT (datetime(\'now\'))
            )',
        );
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_media_kind ON media(kind)');
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_media_created ON media(created_at)');
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array{id: string, kind: string, url: string, filename: string, mime: string, size: int, label: string, created_at: string}
     */
    private function hydrate(array $row): array
    {
        return [
            'id' => (string) ($row['id'] ?? ''),
            'kind' => (string) ($row['kind'] ?? ''),
            'url' => (string) ($row['url'] ?? ''),
            'filename' => (string) ($row['filename'] ?? ''),
            'mime' => (string) ($row['mime'] ?? ''),
            'size' => (int) ($row['size'] ?? 0),
            'label' => (string) ($row['label'] ?? ''),
            'created_at' => (string) ($row['created_at'] ?? ''),
        ];
    }
}
