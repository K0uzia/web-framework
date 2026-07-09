<?php

declare(strict_types=1);

namespace Capsule;

final class SiteExportPath
{
    public function __construct(
        private readonly string $projectRoot,
    ) {
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function resolve(string $input): string
    {
        $input = trim($input);
        if ($input === '') {
            throw new \InvalidArgumentException('Indiquez un emplacement pour le site exporté.');
        }

        $path = $input;
        if (!str_starts_with($path, '/')) {
            $path = $this->projectRoot . '/' . ltrim($path, '/');
        }

        $path = $this->normalize($path);
        $this->assertAllowed($path);

        return $path;
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function assertAllowed(string $path): void
    {
        $root = realpath($this->projectRoot);
        if ($root === false) {
            throw new \InvalidArgumentException('Racine du projet introuvable.');
        }

        $root = rtrim(str_replace('\\', '/', $root), '/');
        $normalized = str_replace('\\', '/', $path);
        $resolved = realpath($normalized);

        if ($resolved !== false) {
            $normalized = str_replace('\\', '/', $resolved);
        }

        if ($normalized === $root) {
            throw new \InvalidArgumentException(
                'Impossible d\'exporter vers la racine du projet. Choisissez un sous-dossier, par exemple « exports/mon-site ».',
            );
        }

        if (!str_starts_with($normalized, $root . '/')) {
            $this->assertOutsideProjectPath($normalized);

            return;
        }

        if (!str_starts_with($normalized, $root . '/exports/')) {
            throw new \InvalidArgumentException(
                'Dans le projet, seul un dossier sous « exports/ » est autorisé (ex. exports/mon-site).',
            );
        }

        $relative = substr($normalized, strlen($root . '/exports/'));
        if ($relative === '' || str_contains($relative, '/..')) {
            throw new \InvalidArgumentException(
                'Indiquez un dossier d\'export précis, par exemple « exports/mon-site ».',
            );
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function assertOutsideProjectPath(string $path): void
    {
        $root = realpath($this->projectRoot);
        if ($root !== false) {
            $root = rtrim(str_replace('\\', '/', $root), '/');
            if ($path === $root || str_starts_with($path, $root . '/')) {
                throw new \InvalidArgumentException(
                    'Cet emplacement est dans le projet. Utilisez « exports/mon-site » ou un dossier en dehors du dépôt.',
                );
            }
        }

        if (in_array($path, ['/', '/home', '/tmp'], true)) {
            throw new \InvalidArgumentException('Cet emplacement est trop large. Choisissez un dossier dédié.');
        }

        if (preg_match('#^/home/[^/]+$#', $path) === 1) {
            throw new \InvalidArgumentException('Choisissez un sous-dossier, pas votre répertoire personnel.');
        }
    }

    private function normalize(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        $isAbsolute = str_starts_with($path, '/');
        $parts = [];

        foreach (explode('/', $path) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }
            if ($segment === '..') {
                if ($parts !== []) {
                    array_pop($parts);
                }

                continue;
            }
            $parts[] = $segment;
        }

        $normalized = implode('/', $parts);

        return $isAbsolute ? '/' . $normalized : $normalized;
    }
}
