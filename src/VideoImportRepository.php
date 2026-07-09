<?php

declare(strict_types=1);

namespace Capsule;

use PDO;

final class VideoImportRepository
{
    public function __construct(private readonly PDO $pdo)
    {
        $this->ensureSchema();
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function create(array $data): array
    {
        $id = 'vid-' . bin2hex(random_bytes(8));
        $stmt = $this->pdo->prepare(
            'INSERT INTO video_imports (
                id, source, source_url, youtube_id, user_label, status, progress, message,
                rights_accepted, requires_approval, approved, max_attempts, owner_id
            ) VALUES (
                :id, :source, :source_url, :youtube_id, :user_label, :status, 0, :message,
                :rights_accepted, :requires_approval, :approved, :max_attempts, :owner_id
            )',
        );
        $stmt->execute([
            'id' => $id,
            'source' => (string) ($data['source'] ?? 'youtube'),
            'source_url' => (string) ($data['source_url'] ?? ''),
            'youtube_id' => (string) ($data['youtube_id'] ?? ''),
            'user_label' => (string) ($data['user_label'] ?? ''),
            'status' => (string) ($data['status'] ?? 'queued'),
            'message' => (string) ($data['message'] ?? ''),
            'rights_accepted' => !empty($data['rights_accepted']) ? 1 : 0,
            'requires_approval' => !empty($data['requires_approval']) ? 1 : 0,
            'approved' => !empty($data['approved']) ? 1 : 0,
            'max_attempts' => (int) ($data['max_attempts'] ?? 3),
            'owner_id' => (string) ($data['owner_id'] ?? 'dev'),
        ]);

        return $this->findById($id) ?? [];
    }

    public function findById(string $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM video_imports WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $this->hydrate($row) : null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function allRecent(int $limit = 50): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM video_imports ORDER BY created_at DESC LIMIT :limit');
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_map($this->hydrate(...), $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
    }

    public function countActiveForOwner(string $ownerId): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM video_imports
             WHERE owner_id = :owner
               AND status IN ('queued', 'downloading', 'converting', 'pending_approval')",
        );
        $stmt->execute(['owner' => $ownerId]);

        return (int) $stmt->fetchColumn();
    }

    public function claimNext(): ?array
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->query(
                "SELECT id FROM video_imports
                 WHERE status = 'queued' AND approved = 1
                 ORDER BY created_at ASC
                 LIMIT 1",
            );
            $id = $stmt !== false ? $stmt->fetchColumn() : false;
            if (!is_string($id) || $id === '') {
                $this->pdo->commit();

                return null;
            }

            $update = $this->pdo->prepare(
                "UPDATE video_imports
                 SET status = 'downloading', progress = 5, message = 'Téléchargement en cours…',
                     attempts = attempts + 1, updated_at = datetime('now')
                 WHERE id = :id AND status = 'queued'",
            );
            $update->execute(['id' => $id]);
            $this->pdo->commit();

            return $this->findById($id);
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * @param array<string, mixed> $fields
     */
    public function update(string $id, array $fields): void
    {
        if ($fields === []) {
            return;
        }

        $sets = [];
        $params = ['id' => $id];
        foreach ($fields as $key => $value) {
            $sets[] = $key . ' = :' . $key;
            $params[$key] = $value;
        }
        $sets[] = "updated_at = datetime('now')";

        $sql = 'UPDATE video_imports SET ' . implode(', ', $sets) . ' WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    public function markFailed(string $id, string $message): void
    {
        $job = $this->findById($id);
        if ($job === null) {
            return;
        }

        $retry = (int) $job['attempts'] < (int) $job['max_attempts'];
        $this->update($id, [
            'status' => $retry ? 'queued' : 'failed',
            'progress' => 0,
            'message' => $message,
        ]);
    }

    public function approve(string $id): bool
    {
        $job = $this->findById($id);
        if ($job === null || $job['status'] !== 'pending_approval') {
            return false;
        }

        $this->update($id, [
            'approved' => 1,
            'status' => 'queued',
            'message' => 'Approuvé, en attente de traitement.',
        ]);

        return true;
    }

    public function countByStatus(string $status): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM video_imports WHERE status = :status');
        $stmt->execute(['status' => $status]);

        return (int) $stmt->fetchColumn();
    }

    public function delete(string $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM video_imports WHERE id = :id');

        return $stmt->execute(['id' => $id]) && $stmt->rowCount() > 0;
    }

    public function totalDiskUsageBytes(): int
    {
        $stmt = $this->pdo->query('SELECT COALESCE(SUM(file_size), 0) FROM video_imports WHERE status = \'ready\'');

        return (int) ($stmt !== false ? $stmt->fetchColumn() : 0);
    }

    private function ensureSchema(): void
    {
        $this->pdo->exec(
            "CREATE TABLE IF NOT EXISTS video_imports (
                id TEXT PRIMARY KEY,
                source TEXT NOT NULL CHECK (source IN ('youtube', 'upload')),
                source_url TEXT NOT NULL DEFAULT '',
                youtube_id TEXT NOT NULL DEFAULT '',
                user_label TEXT NOT NULL DEFAULT '',
                title TEXT NOT NULL DEFAULT '',
                duration_sec INTEGER NOT NULL DEFAULT 0,
                video_path TEXT NOT NULL DEFAULT '',
                thumb_path TEXT NOT NULL DEFAULT '',
                public_video_url TEXT NOT NULL DEFAULT '',
                public_thumb_url TEXT NOT NULL DEFAULT '',
                media_id TEXT NOT NULL DEFAULT '',
                status TEXT NOT NULL DEFAULT 'queued' CHECK (status IN ('queued', 'downloading', 'converting', 'ready', 'failed', 'pending_approval')),
                progress INTEGER NOT NULL DEFAULT 0,
                message TEXT NOT NULL DEFAULT '',
                rights_accepted INTEGER NOT NULL DEFAULT 0,
                requires_approval INTEGER NOT NULL DEFAULT 0,
                approved INTEGER NOT NULL DEFAULT 1,
                attempts INTEGER NOT NULL DEFAULT 0,
                max_attempts INTEGER NOT NULL DEFAULT 3,
                file_size INTEGER NOT NULL DEFAULT 0,
                format TEXT NOT NULL DEFAULT 'mp4',
                owner_id TEXT NOT NULL DEFAULT 'dev',
                created_at TEXT NOT NULL DEFAULT (datetime('now')),
                updated_at TEXT NOT NULL DEFAULT (datetime('now'))
            )",
        );
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_video_imports_status ON video_imports(status)');
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_video_imports_owner ON video_imports(owner_id)');
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_video_imports_created ON video_imports(created_at)');
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    private function hydrate(array $row): array
    {
        return [
            'id' => (string) ($row['id'] ?? ''),
            'source' => (string) ($row['source'] ?? ''),
            'source_url' => (string) ($row['source_url'] ?? ''),
            'youtube_id' => (string) ($row['youtube_id'] ?? ''),
            'user_label' => (string) ($row['user_label'] ?? ''),
            'title' => (string) ($row['title'] ?? ''),
            'duration_sec' => (int) ($row['duration_sec'] ?? 0),
            'video_path' => (string) ($row['video_path'] ?? ''),
            'thumb_path' => (string) ($row['thumb_path'] ?? ''),
            'public_video_url' => (string) ($row['public_video_url'] ?? ''),
            'public_thumb_url' => (string) ($row['public_thumb_url'] ?? ''),
            'media_id' => (string) ($row['media_id'] ?? ''),
            'status' => (string) ($row['status'] ?? ''),
            'progress' => (int) ($row['progress'] ?? 0),
            'message' => (string) ($row['message'] ?? ''),
            'rights_accepted' => (int) ($row['rights_accepted'] ?? 0) === 1,
            'requires_approval' => (int) ($row['requires_approval'] ?? 0) === 1,
            'approved' => (int) ($row['approved'] ?? 0) === 1,
            'attempts' => (int) ($row['attempts'] ?? 0),
            'max_attempts' => (int) ($row['max_attempts'] ?? 3),
            'file_size' => (int) ($row['file_size'] ?? 0),
            'format' => (string) ($row['format'] ?? 'mp4'),
            'owner_id' => (string) ($row['owner_id'] ?? ''),
            'created_at' => (string) ($row['created_at'] ?? ''),
            'updated_at' => (string) ($row['updated_at'] ?? ''),
        ];
    }
}
