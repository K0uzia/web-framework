<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Préfixe URL pour un déploiement en sous-dossier (ex. /mon-app/).
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

        self::ensureBootstrap();

        return new self(capsule_base_path_detect());
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
        self::ensureBootstrap();

        return capsule_base_path_strip($requestPath, $this->path);
    }

    /**
     * Retire le préfixe détecté (filet de sécurité si APP_BASE_PATH est absent).
     */
    public static function stripDetectedPrefix(string $path): string
    {
        self::ensureBootstrap();

        return capsule_base_path_strip($path);
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

        if ($path === $this->path || str_starts_with($path, $this->path . '/')) {
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

        $prefix = $this->path;

        $rewritten = preg_replace_callback(
            '#(?<attr>href|src|action)=(["\'])(?<url>/[^"\']*)#',
            static function (array $matches) use ($prefix): string {
                $url = $matches['url'];
                if (str_starts_with($url, '//')
                    || $url === $prefix
                    || str_starts_with($url, $prefix . '/')) {
                    return $matches['attr'] . '=' . $matches[2] . $url;
                }

                return $matches['attr'] . '=' . $matches[2] . $prefix . $url;
            },
            $html,
        );

        return $rewritten ?? $html;
    }

    private static function normalize(string $path): string
    {
        $path = '/' . trim($path, '/');

        return $path === '/' ? '' : $path;
    }

    private static function ensureBootstrap(): void
    {
        static $loaded = false;
        if ($loaded) {
            return;
        }
        $loaded = true;

        require_once dirname(__DIR__) . '/bootstrap/base-path.php';
    }
}
