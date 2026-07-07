<?php

declare(strict_types=1);

namespace Capsule;

use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;

/**
 * Sert une vidéo locale avec support des requêtes Range (streaming).
 */
final class VideoStreamResponder
{
    public function respond(Request $request, string $absolutePath, string $mime = 'video/mp4'): Response
    {
        if (!is_file($absolutePath) || !is_readable($absolutePath)) {
            return new Response(404, ['Content-Type' => 'text/plain; charset=utf-8'], 'Fichier introuvable');
        }

        $size = filesize($absolutePath);
        if ($size === false) {
            return new Response(500, ['Content-Type' => 'text/plain; charset=utf-8'], 'Erreur lecture fichier');
        }

        $range = $this->parseRange($request->headers['range'] ?? $request->headers['Range'] ?? null, $size);
        if ($range === null) {
            $body = file_get_contents($absolutePath);

            return new Response(200, [
                'Content-Type' => $mime,
                'Content-Length' => (string) $size,
                'Accept-Ranges' => 'bytes',
                'Cache-Control' => 'private, max-age=3600',
            ], $body === false ? '' : $body);
        }

        [$start, $end] = $range;
        $length = $end - $start + 1;
        $handle = fopen($absolutePath, 'rb');
        if ($handle === false) {
            return new Response(500, ['Content-Type' => 'text/plain; charset=utf-8'], 'Erreur lecture fichier');
        }

        fseek($handle, $start);
        $body = fread($handle, $length) ?: '';
        fclose($handle);

        return new Response(206, [
            'Content-Type' => $mime,
            'Content-Length' => (string) $length,
            'Content-Range' => 'bytes ' . $start . '-' . $end . '/' . $size,
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'private, max-age=3600',
        ], $body);
    }

    /**
     * @return array{0: int, 1: int}|null
     */
    private function parseRange(mixed $header, int $size): ?array
    {
        if (!is_string($header) || !preg_match('/bytes=(\d+)-(\d*)/', $header, $m)) {
            return null;
        }

        $start = (int) $m[1];
        $end = $m[2] !== '' ? (int) $m[2] : ($size - 1);
        if ($start > $end || $end >= $size) {
            return null;
        }

        return [$start, $end];
    }
}
