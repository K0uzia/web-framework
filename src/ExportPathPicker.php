<?php

declare(strict_types=1);

namespace Capsule;

final class ExportPathPicker
{
    public function __construct(
        private readonly string $projectRoot,
    ) {
    }

    public function isAvailable(): bool
    {
        return $this->command() !== null;
    }

    /**
     * Ouvre un sélecteur de dossier natif (zenity ou kdialog). Réservé au dev local.
     */
    public function pick(?string $initialDir = null): ?string
    {
        $command = $this->command();
        if ($command === null) {
            return null;
        }

        $start = $initialDir ?? $this->projectRoot . '/exports';
        if (!is_dir($start)) {
            $start = $this->projectRoot;
        }

        $startArg = escapeshellarg($start);

        if ($command === 'zenity') {
            $line = $this->run($command . ' --file-selection --directory --title=' . escapeshellarg('Dossier d\'export du site') . ' --filename=' . $startArg . '/');
        } else {
            $line = $this->run($command . ' --getexistingdirectory ' . $startArg . ' --title ' . escapeshellarg('Dossier d\'export du site'));
        }

        if ($line === null || $line === '') {
            return null;
        }

        return $line;
    }

    private function command(): ?string
    {
        foreach (['zenity', 'kdialog'] as $name) {
            $path = $this->which($name);
            if ($path !== null) {
                return $name;
            }
        }

        return null;
    }

    private function which(string $name): ?string
    {
        $path = trim((string) shell_exec('command -v ' . escapeshellarg($name) . ' 2>/dev/null'));

        return $path !== '' ? $path : null;
    }

    private function run(string $command): ?string
    {
        $output = [];
        $exitCode = 0;
        exec($command . ' 2>/dev/null', $output, $exitCode);

        if ($exitCode !== 0) {
            return null;
        }

        $line = trim(implode("\n", $output));

        return $line !== '' ? $line : null;
    }
}
