<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Valide et normalise les URLs YouTube autorisées.
 */
final class YouTubeUrlValidator
{
  /** @var list<string> */
    private const ALLOWED_HOSTS = [
        'www.youtube.com',
        'youtube.com',
        'm.youtube.com',
        'music.youtube.com',
        'youtu.be',
        'www.youtube-nocookie.com',
    ];

    public function isAllowedUrl(string $url): bool
    {
        return $this->extractVideoId($url) !== null;
    }

    public function extractVideoId(string $url): ?string
    {
        $url = trim($url);
        if ($url === '') {
            return null;
        }

        if (preg_match('~^([a-zA-Z0-9_-]{6,})$~', $url) === 1) {
            return $url;
        }

        $parts = parse_url($url);
        if (!is_array($parts)) {
            return null;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        if (!in_array($host, self::ALLOWED_HOSTS, true)) {
            return null;
        }

        if ($host === 'youtu.be') {
            $id = trim((string) ($parts['path'] ?? ''), '/');

            return preg_match('~^[a-zA-Z0-9_-]{6,}$~', $id) === 1 ? $id : null;
        }

        $path = (string) ($parts['path'] ?? '');
        if (preg_match('~^/embed/([a-zA-Z0-9_-]{6,})~', $path, $m) === 1) {
            return $m[1];
        }

        parse_str((string) ($parts['query'] ?? ''), $query);
        $id = (string) ($query['v'] ?? '');

        return preg_match('~^[a-zA-Z0-9_-]{6,}$~', $id) === 1 ? $id : null;
    }

    public function canonicalWatchUrl(string $videoId): string
    {
        return 'https://www.youtube.com/watch?v=' . rawurlencode($videoId);
    }
}
