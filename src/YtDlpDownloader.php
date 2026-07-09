<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Télécharge une vidéo YouTube via yt-dlp (sans shell).
 */
final class YtDlpDownloader
{
    public function __construct(
        private readonly ProcessRunner $runner,
        private readonly VideoImportConfig $config,
        private readonly YouTubeUrlValidator $validator,
    ) {
    }

    /**
     * @return array{video_path: string, thumb_path: string, title: string, duration_sec: int, info_path: string}
     */
    public function download(string $importId, string $sourceUrl): array
    {
        if (!$this->runner->isExecutable($this->config->ytDlpBin)) {
            throw new \RuntimeException('yt-dlp introuvable. Consultez doc/video-imports.md');
        }

        $videoId = $this->validator->extractVideoId($sourceUrl);
        if ($videoId === null) {
            throw new \InvalidArgumentException('URL YouTube invalide.');
        }

        $dir = $this->config->jobDir($importId);
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new \RuntimeException('Impossible de créer le dossier d\'import.');
        }

        $errors = [];
        foreach ($this->commandStrategies($videoId, $dir) as $label => $command) {
            $this->clearDownloadArtifacts($dir);
            $result = $this->runner->run($command, $dir, 7200);
            if ($result->successful()) {
                return $this->collectDownload($dir, $videoId);
            }

            $errors[] = $label . ' : ' . $result->tail(1200);
        }

        $hint = $this->versionHint();
        $message = implode(' | ', $errors);
        if ($hint !== '') {
            $message .= ' | ' . $hint;
        }

        throw new \RuntimeException('yt-dlp a échoué : ' . $message);
    }

    public function version(): ?string
    {
        if (!$this->runner->isExecutable($this->config->ytDlpBin)) {
            return null;
        }

        try {
            $result = $this->runner->run([$this->config->ytDlpBin, '--version'], null, 15);
        } catch (\Throwable) {
            return null;
        }

        $version = trim($result->stdout);

        return $version !== '' ? $version : null;
    }

    public function isVersionLikelyOutdated(?string $version = null): bool
    {
        $version ??= $this->version();
        if ($version === null) {
            return true;
        }

        if (preg_match('/^(\d{4})\.(\d{2})\.(\d{2})/', $version, $m) !== 1) {
            return false;
        }

        $stamp = gmmktime(0, 0, 0, (int) $m[2], (int) $m[3], (int) $m[1]);

        return $stamp < gmmktime(0, 0, 0, 1, 1, 2025);
    }

    /**
     * @return array<string, list<string>>
     */
    private function commandStrategies(string $videoId, string $dir): array
    {
        $url = $this->validator->canonicalWatchUrl($videoId);
        $outputTemplate = $dir . '/%(id)s.%(ext)s';
        $extraArgs = $this->isVersionLikelyOutdated() ? [] : $this->config->ytDlpExtraArgs();

        $base = [
            $this->config->ytDlpBin,
            '--no-playlist',
            '--write-info-json',
            '--write-thumbnail',
            '--retries', '3',
            '--fragment-retries', '3',
            '--socket-timeout', '60',
            '-f', 'bv*+ba/b',
            '--merge-output-format', 'mp4',
            '-o', $outputTemplate,
            ...$extraArgs,
        ];

        $clients = $this->config->ytDlpPlayerClients();
        $strategies = [];
        foreach ($clients as $client) {
            $strategies['client ' . $client] = [
                ...$base,
                '--extractor-args', 'youtube:player_client=' . $client,
                $url,
            ];
        }

        return $strategies;
    }

    /**
     * @return array{video_path: string, thumb_path: string, title: string, duration_sec: int, info_path: string}
     */
    private function collectDownload(string $dir, string $videoId): array
    {
        $videoPath = $this->findByExtension($dir, ['mp4', 'mkv', 'webm', 'm4a']);
        if ($videoPath === null) {
            throw new \RuntimeException('Fichier vidéo introuvable après téléchargement.');
        }

        $thumbPath = $this->findByExtension($dir, ['jpg', 'jpeg', 'png', 'webp']) ?? '';
        $infoPath = $this->findInfoJson($dir);
        $meta = $this->readInfoJson($infoPath);

        return [
            'video_path' => $videoPath,
            'thumb_path' => $thumbPath,
            'title' => (string) ($meta['title'] ?? $videoId),
            'duration_sec' => (int) ($meta['duration'] ?? 0),
            'info_path' => $infoPath ?? '',
        ];
    }

    private function clearDownloadArtifacts(string $dir): void
    {
        foreach (scandir($dir) ?: [] as $file) {
            if ($file === '.' || $file === '..' || str_starts_with($file, 'upload-source.')) {
                continue;
            }
            $path = $dir . '/' . $file;
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    private function versionHint(): string
    {
        if (!$this->isVersionLikelyOutdated()) {
            return '';
        }

        $version = $this->version() ?? 'inconnue';

        return 'yt-dlp semble obsolète (' . $version . '). Mettez à jour : bash scripts/install-video-tools.sh';
    }

    /**
     * @param list<string> $extensions
     */
    private function findByExtension(string $dir, array $extensions): ?string
    {
        foreach (scandir($dir) ?: [] as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, $extensions, true)) {
                return $dir . '/' . $file;
            }
        }

        return null;
    }

    private function findInfoJson(string $dir): ?string
    {
        foreach (scandir($dir) ?: [] as $file) {
            if (str_ends_with($file, '.info.json')) {
                return $dir . '/' . $file;
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function readInfoJson(?string $path): array
    {
        if ($path === null || !is_file($path)) {
            return [];
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }
}
