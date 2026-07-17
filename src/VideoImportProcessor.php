<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Orchestre le traitement d'un job d'import vidéo.
 */
final class VideoImportProcessor
{
    public function __construct(
        private readonly VideoImportRepository $imports,
        private readonly MediaRepository $media,
        private readonly VideoImportConfig $config,
        private readonly YtDlpDownloader $ytDlp,
        private readonly FfmpegConverter $ffmpeg,
    ) {
    }

    /**
     * @param array<string, mixed> $job
     */
    public function process(array $job): void
    {
        $id = (string) $job['id'];
        if ($this->imports->findById($id) === null) {
            return;
        }

        $dir = $this->config->jobDir($id);
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new \RuntimeException('Dossier d\'import inaccessible.');
        }

        $this->assertDiskQuota();

        $source = (string) $job['source'];
        if ($source === 'youtube') {
            $this->processYouTube($job);
        } elseif ($source === 'upload') {
            $this->processUpload($job);
        } else {
            throw new \InvalidArgumentException('Source inconnue : ' . $source);
        }
    }

    /**
     * @param array<string, mixed> $job
     */
    private function processYouTube(array $job): void
    {
        $id = (string) $job['id'];
        $download = $this->ytDlp->download($id, (string) $job['source_url']);

        $this->imports->update($id, [
            'title' => $download['title'],
            'duration_sec' => $download['duration_sec'],
            'progress' => 55,
            'message' => 'Téléchargement terminé, conversion en cours…',
            'status' => 'converting',
        ]);

        $this->finalizeVideo($job, $download['video_path'], $download['thumb_path'], $download['title']);
    }

    /**
     * @param array<string, mixed> $job
     */
    private function processUpload(array $job): void
    {
        $id = (string) $job['id'];
        $input = (string) $job['video_path'];
        if ($input === '' || !is_file($input)) {
            throw new \RuntimeException('Fichier uploadé introuvable.');
        }

        $this->imports->update($id, [
            'progress' => 40,
            'message' => 'Conversion du fichier uploadé…',
            'status' => 'converting',
        ]);

        $this->finalizeVideo($job, $input, (string) $job['thumb_path'], (string) ($job['title'] !== '' ? $job['title'] : basename($input)));
    }

    /**
     * @param array<string, mixed> $job
     */
    private function finalizeVideo(array $job, string $inputVideo, string $inputThumb, string $title): void
    {
        $id = (string) $job['id'];
        $dir = $this->config->jobDir($id);
        $finalVideo = $dir . '/video.mp4';
        $finalThumb = $dir . '/thumb.jpg';
        $publicBase = $this->config->publicJobBase($id);

        if ($this->ffmpeg->needsConversion($inputVideo) || $inputVideo !== $finalVideo) {
            $this->ffmpeg->toBrowserMp4($inputVideo, $finalVideo);
        } elseif (!copy($inputVideo, $finalVideo)) {
            throw new \RuntimeException('Impossible de copier la vidéo finale.');
        }

        $size = (int) filesize($finalVideo);
        if ($size <= 0 || $size > $this->config->maxFileBytes) {
            throw new \RuntimeException('Taille de fichier hors limites autorisées.');
        }

        $thumbSaved = $this->ffmpeg->normalizeThumbnail($inputThumb, $finalThumb);
        $publicThumb = $thumbSaved !== '' ? $publicBase . '/thumb.jpg' : '';

        $media = $this->media->create(
            'video',
            $publicBase . '/video.mp4',
            'video.mp4',
            'video/mp4',
            $size,
            $title,
            MediaRepository::OWNER_DEV,
        );

        $this->imports->update($id, [
            'title' => $title,
            'video_path' => $finalVideo,
            'thumb_path' => $thumbSaved,
            'public_video_url' => $publicBase . '/video.mp4',
            'public_thumb_url' => $publicThumb,
            'media_id' => $media['id'],
            'file_size' => $size,
            'format' => 'mp4',
            'status' => 'ready',
            'progress' => 100,
            'message' => 'Vidéo prête.',
        ]);
    }

    private function assertDiskQuota(): void
    {
        $used = $this->imports->totalDiskUsageBytes();
        if ($used >= $this->config->diskQuotaBytes) {
            throw new \RuntimeException('Quota disque atteint pour les imports vidéo.');
        }
    }
}
