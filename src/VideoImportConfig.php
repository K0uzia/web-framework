<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Configuration du pipeline d'import vidéo (yt-dlp, ffmpeg, quotas).
 */
final class VideoImportConfig
{
    public function __construct(
        public readonly string $importsRoot,
        public readonly string $publicBasePath,
        public readonly string $ytDlpBin,
        public readonly string $ffmpegBin,
        public readonly int $maxFileBytes,
        public readonly int $maxQueuePerOwner,
        public readonly bool $requireApproval,
        public readonly int $maxAttempts,
        public readonly int $diskQuotaBytes,
        /** @var list<string> */
        public readonly array $ytDlpPlayerClients = ['android,web', 'ios', 'tv_embedded', 'mweb'],
        /** @var list<string> */
        public readonly array $ytDlpExtraArgs = [],
    ) {
    }

    public static function fromEnv(string $projectRoot): self
    {
        self::loadOptionalEnvFile($projectRoot . '/config/video-tools.env');

        $importsRoot = self::envPath('VIDEO_IMPORT_ROOT', $projectRoot . '/public/uploads/media/imports');
        $publicBase = rtrim((string) ($_ENV['VIDEO_IMPORT_PUBLIC_BASE'] ?? '/uploads/media/imports'), '/');
        $clients = self::envList('VIDEO_IMPORT_YT_DLP_CLIENTS', ['android,web', 'ios', 'tv_embedded', 'mweb']);
        $extraArgs = self::envArgs('VIDEO_IMPORT_YT_DLP_EXTRA_ARGS', ['--remote-components', 'ejs:github']);

        return new self(
            importsRoot: $importsRoot,
            publicBasePath: $publicBase,
            ytDlpBin: (string) ($_ENV['VIDEO_IMPORT_YT_DLP_BIN'] ?? self::resolveYtDlpBin($projectRoot)),
            ffmpegBin: (string) ($_ENV['VIDEO_IMPORT_FFMPEG_BIN'] ?? 'ffmpeg'),
            maxFileBytes: self::envInt('VIDEO_IMPORT_MAX_BYTES', 500 * 1024 * 1024),
            maxQueuePerOwner: self::envInt('VIDEO_IMPORT_MAX_QUEUE', 5),
            requireApproval: self::envBool('VIDEO_IMPORT_REQUIRE_APPROVAL', false),
            maxAttempts: self::envInt('VIDEO_IMPORT_MAX_ATTEMPTS', 3),
            diskQuotaBytes: self::envInt('VIDEO_IMPORT_DISK_QUOTA_BYTES', 5 * 1024 * 1024 * 1024),
            ytDlpPlayerClients: $clients,
            ytDlpExtraArgs: $extraArgs,
        );
    }

    /**
     * @return list<string>
     */
    public function ytDlpPlayerClients(): array
    {
        return $this->ytDlpPlayerClients;
    }

    /**
     * @return list<string>
     */
    public function ytDlpExtraArgs(): array
    {
        return $this->ytDlpExtraArgs;
    }

    private static function resolveYtDlpBin(string $projectRoot): string
    {
        $candidates = [
            $projectRoot . '/tools/video-tools-venv/bin/yt-dlp',
            $projectRoot . '/tools/bin/yt-dlp',
            '/usr/local/bin/yt-dlp',
            getenv('HOME') !== false ? getenv('HOME') . '/.local/bin/yt-dlp' : '',
            'yt-dlp',
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '' && is_executable($candidate)) {
                return $candidate;
            }
        }

        return 'yt-dlp';
    }

    /**
     * @return list<string>
     */
    private static function envList(string $key, array $default): array
    {
        $value = $_ENV[$key] ?? getenv($key);
        if (!is_string($value) || trim($value) === '') {
            return $default;
        }

        $parts = array_values(array_filter(array_map('trim', explode(',', $value)), static fn (string $v): bool => $v !== ''));

        return $parts !== [] ? $parts : $default;
    }

    /**
     * @return list<string>
     */
    private static function envArgs(string $key, array $default): array
    {
        $value = $_ENV[$key] ?? getenv($key);
        if (!is_string($value) || trim($value) === '') {
            return $default;
        }

        $parts = preg_split('/\s+/', trim($value)) ?: [];

        return $parts !== [] ? array_values($parts) : $default;
    }

    public function jobDir(string $id): string
    {
        return rtrim($this->importsRoot, '/') . '/' . $id;
    }

    public function publicJobBase(string $id): string
    {
        return rtrim($this->publicBasePath, '/') . '/' . $id;
    }

    private static function envInt(string $key, int $default): int
    {
        $value = $_ENV[$key] ?? getenv($key);
        if (!is_string($value) || $value === '' || !ctype_digit($value)) {
            return $default;
        }

        return (int) $value;
    }

    private static function envBool(string $key, bool $default): bool
    {
        $value = $_ENV[$key] ?? getenv($key);
        if (!is_string($value) || $value === '') {
            return $default;
        }

        return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
    }

    private static function envPath(string $key, string $default): string
    {
        $value = $_ENV[$key] ?? getenv($key);

        return is_string($value) && $value !== '' ? $value : $default;
    }

    private static function loadOptionalEnvFile(string $path): void
    {
        if (!is_file($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (str_starts_with($line, 'export ')) {
                $line = substr($line, 7);
            }
            if (!str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\"'");
            if ($key !== '' && !isset($_ENV[$key])) {
                $_ENV[$key] = $value;
                putenv($key . '=' . $value);
            }
        }
    }
}
