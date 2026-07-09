<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Convertit une vidéo en MP4 H.264/AAC compatible navigateur.
 */
final class FfmpegConverter
{
    public function __construct(
        private readonly ProcessRunner $runner,
        private readonly VideoImportConfig $config,
    ) {
    }

    public function toBrowserMp4(string $inputPath, string $outputPath): void
    {
        if (!$this->runner->isExecutable($this->config->ffmpegBin)) {
            throw new \RuntimeException('ffmpeg introuvable. Consultez doc/video-imports.md');
        }

        if (!is_file($inputPath)) {
            throw new \InvalidArgumentException('Fichier source introuvable.');
        }

        $outputDir = dirname($outputPath);
        if (!is_dir($outputDir) && !mkdir($outputDir, 0775, true) && !is_dir($outputDir)) {
            throw new \RuntimeException('Impossible de créer le dossier de sortie ffmpeg.');
        }

        if (is_file($outputPath)) {
            unlink($outputPath);
        }

        $command = [
            $this->config->ffmpegBin,
            '-y',
            '-i', $inputPath,
            '-c:v', 'libx264',
            '-preset', 'fast',
            '-crf', '23',
            '-c:a', 'aac',
            '-b:a', '128k',
            '-movflags', '+faststart',
            $outputPath,
        ];

        $result = $this->runner->run($command, null, 7200);
        if (!$result->successful() || !is_file($outputPath)) {
            throw new \RuntimeException('ffmpeg a échoué : ' . $result->tail());
        }
    }

    public function needsConversion(string $path): bool
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return $ext !== 'mp4';
    }

    public function normalizeThumbnail(string $sourceThumb, string $targetJpg): string
    {
        if ($sourceThumb === '' || !is_file($sourceThumb)) {
            return '';
        }

        $ext = strtolower(pathinfo($sourceThumb, PATHINFO_EXTENSION));
        if ($ext === 'jpg' || $ext === 'jpeg') {
            if ($sourceThumb !== $targetJpg) {
                copy($sourceThumb, $targetJpg);
            }

            return $targetJpg;
        }

        if (!$this->runner->isExecutable($this->config->ffmpegBin)) {
            return '';
        }

        $command = [
            $this->config->ffmpegBin,
            '-y',
            '-i', $sourceThumb,
            '-q:v', '2',
            $targetJpg,
        ];
        $result = $this->runner->run($command, null, 120);
        if (!$result->successful() || !is_file($targetJpg)) {
            return '';
        }

        return $targetJpg;
    }
}
