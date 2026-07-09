<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Préfixe URL pour un déploiement en sous-dossier (ex. /wf/).
 */
final class BasePath
{
    public function __construct(private readonly string $path)
    {
    }

    public static function fromEnv(string $envValue = ''): self
    {
        $trimmed = trim($envValue);
        if ($trimmed !== '') {
            return new self(self::normalize($trimmed));
        }

        $detected = self::detectFromScriptName();
        if ($detected === '') {
            $detected = self::detectFromRequestUri();
        }

        return new self($detected);
    }

    public function value(): string
    {
        return $this->path;
    }

    public function isEmpty(): bool
    {
        return $this->path === '';
    }

    /**
     * Retire le préfixe du chemin entrant (REQUEST_URI).
     */
    public function strip(string $requestPath): string
    {
        if ($this->path === '') {
            return $requestPath;
        }

        if ($requestPath === $this->path || $requestPath === $this->path . '/') {
            return '/';
        }

        if (str_starts_with($requestPath, $this->path . '/')) {
            $rest = substr($requestPath, strlen($this->path));

            return $rest === '' ? '/' : $rest;
        }

        return $requestPath;
    }

    /**
     * Préfixe un chemin absolu (/dev, /assets/…).
     */
    public function url(string $path): string
    {
        if ($path === '' || $path[0] !== '/') {
            return $path;
        }

        if ($this->path === '') {
            return $path;
        }

        return $this->path . $path;
    }

    public function cookiePath(string $path): string
    {
        if ($path === '' || $path[0] !== '/') {
            $path = '/' . ltrim($path, '/');
        }

        return $this->url($path);
    }

    public function rewriteHtml(string $html): string
    {
        if ($this->path === '') {
            return $html;
        }

        $rewritten = preg_replace_callback(
            '#(?<attr>href|src|action)=(["\'])/(?!/)#',
            fn (array $matches): string => $matches['attr'] . '=' . $matches[2] . $this->path . '/',
            $html,
        );

        return $rewritten ?? $html;
    }

    private static function normalize(string $path): string
    {
        $path = '/' . trim($path, '/');

        return $path === '/' ? '' : $path;
    }

    private static function detectFromScriptName(): string
    {
        $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        if ($script === '' || $script === '/index.php') {
            return '';
        }

        $dir = dirname($script);
        if ($dir === '/' || $dir === '.') {
            return '';
        }

        return $dir;
    }

    /**
     * Détection mutualisée : REQUEST_URI commence par /wf/ (lacapsule.org).
     */
    private static function detectFromRequestUri(): string
    {
        $uri = strtok((string) ($_SERVER['REQUEST_URI'] ?? '/'), '?') ?: '/';
        $uri = rawurldecode($uri);

        if ($uri === '/wf' || str_starts_with($uri, '/wf/')) {
            return '/wf';
        }

        return '';
    }
}
