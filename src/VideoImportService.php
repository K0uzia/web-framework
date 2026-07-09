<?php

declare(strict_types=1);

namespace Capsule;

/**
 * API métier : mise en file, validation, statut.
 */
final class VideoImportService
{
    public function __construct(
        private readonly VideoImportRepository $imports,
        private readonly VideoImportConfig $config,
        private readonly YouTubeUrlValidator $validator,
        private readonly VideoImportCleaner $cleaner,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function enqueueYouTube(string $url, bool $rightsAccepted, string $ownerId = 'dev', string $label = ''): array
    {
        $this->assertRights($rightsAccepted);
        $this->assertQueueLimit($ownerId);

        $videoId = $this->validator->extractVideoId($url);
        if ($videoId === null) {
            throw new \InvalidArgumentException('URL YouTube non autorisée ou invalide.');
        }

        $status = $this->config->requireApproval ? 'pending_approval' : 'queued';
        $approved = $this->config->requireApproval ? 0 : 1;

        return $this->imports->create([
            'source' => 'youtube',
            'source_url' => $this->validator->canonicalWatchUrl($videoId),
            'youtube_id' => $videoId,
            'user_label' => $label,
            'status' => $status,
            'message' => $status === 'pending_approval'
                ? 'En attente d\'approbation administrateur.'
                : 'En file d\'attente.',
            'rights_accepted' => true,
            'requires_approval' => $this->config->requireApproval,
            'approved' => $approved,
            'max_attempts' => $this->config->maxAttempts,
            'owner_id' => $ownerId,
        ]);
    }

    /**
     * @param array{name?: string, type?: string, tmp_name?: string, error?: int, size?: int} $file
     *
     * @return array<string, mixed>
     */
    public function enqueueUpload(array $file, bool $rightsAccepted, string $ownerId = 'dev', string $label = ''): array
    {
        $this->assertRights($rightsAccepted);
        $this->assertQueueLimit($ownerId);

        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($error !== UPLOAD_ERR_OK) {
            throw new \InvalidArgumentException('Échec du transfert du fichier.');
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new \InvalidArgumentException('Fichier uploadé invalide.');
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > $this->config->maxFileBytes) {
            throw new \InvalidArgumentException('Fichier trop volumineux.');
        }

        $mime = (string) ($file['type'] ?? '');
        $allowed = ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime', 'video/x-matroska'];
        if (!in_array($mime, $allowed, true)) {
            throw new \InvalidArgumentException('Format vidéo non pris en charge.');
        }

        $job = $this->imports->create([
            'source' => 'upload',
            'source_url' => '',
            'user_label' => $label,
            'title' => $label !== '' ? $label : (string) ($file['name'] ?? 'Vidéo uploadée'),
            'status' => 'queued',
            'message' => 'Fichier reçu, en file d\'attente.',
            'rights_accepted' => true,
            'requires_approval' => false,
            'approved' => 1,
            'max_attempts' => $this->config->maxAttempts,
            'owner_id' => $ownerId,
        ]);

        $dir = $this->config->jobDir((string) $job['id']);
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new \RuntimeException('Impossible de préparer le stockage.');
        }

        $ext = pathinfo((string) ($file['name'] ?? 'upload.bin'), PATHINFO_EXTENSION) ?: 'bin';
        $dest = $dir . '/upload-source.' . strtolower($ext);
        if (!move_uploaded_file($tmp, $dest)) {
            throw new \RuntimeException('Impossible d\'enregistrer le fichier uploadé.');
        }

        $this->imports->update((string) $job['id'], [
            'video_path' => $dest,
            'file_size' => $size,
        ]);

        return $this->imports->findById((string) $job['id']) ?? $job;
    }

    /**
     * @return array{status: string, progress: int, message: string, title: string, public_video_url: string, public_thumb_url: string, media_id: string}
     */
    public function statusPayload(string $id): array
    {
        $job = $this->imports->findById($id);
        if ($job === null) {
            throw new \InvalidArgumentException('Import introuvable.');
        }

        return [
            'status' => (string) $job['status'],
            'progress' => (int) $job['progress'],
            'message' => (string) $job['message'],
            'title' => (string) $job['title'],
            'public_video_url' => (string) $job['public_video_url'],
            'public_thumb_url' => (string) $job['public_thumb_url'],
            'media_id' => (string) $job['media_id'],
        ];
    }

    public function approve(string $id): bool
    {
        return $this->imports->approve($id);
    }

    public function remove(string $id): void
    {
        $job = $this->imports->findById($id);
        if ($job === null) {
            throw new \InvalidArgumentException('Import introuvable.');
        }

        if (in_array($job['status'], ['downloading', 'converting'], true)) {
            throw new \RuntimeException('Import en cours de traitement. Réessayez dans quelques instants.');
        }

        $this->cleaner->remove($job);
        if (!$this->imports->delete($id)) {
            throw new \RuntimeException('Impossible de supprimer l\'import.');
        }
    }

    /**
     * @return array{yt_dlp: bool, ffmpeg: bool, queued: int, pending: int}
     */
    public function diagnostics(ProcessRunner $runner, ?YtDlpDownloader $ytDlp = null): array
    {
        $version = $ytDlp?->version();
        $outdated = $ytDlp?->isVersionLikelyOutdated($version) ?? true;

        return [
            'yt_dlp' => $runner->isExecutable($this->config->ytDlpBin),
            'ffmpeg' => $runner->isExecutable($this->config->ffmpegBin),
            'queued' => $this->imports->countByStatus('queued'),
            'pending' => $this->imports->countByStatus('pending_approval'),
            'yt_dlp_version' => $version ?? '',
            'yt_dlp_outdated' => $outdated,
            'yt_dlp_bin' => $this->config->ytDlpBin,
        ];
    }

    private function assertRights(bool $rightsAccepted): void
    {
        if (!$rightsAccepted) {
            throw new \InvalidArgumentException('Vous devez confirmer disposer des droits sur ce contenu.');
        }
    }

    private function assertQueueLimit(string $ownerId): void
    {
        if ($this->imports->countActiveForOwner($ownerId) >= $this->config->maxQueuePerOwner) {
            throw new \RuntimeException('Limite de files d\'attente atteinte pour cet utilisateur.');
        }
    }
}
