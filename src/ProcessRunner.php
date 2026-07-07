<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Exécute des binaires externes sans shell (protection injection).
 */
final class ProcessRunner
{
    /** @var (callable)|null */
    private readonly mixed $procOpener;

    public function __construct(
        ?callable $procOpener = null,
    ) {
        $this->procOpener = $procOpener;
    }

    /**
     * @param list<string> $command
     */
    public function run(array $command, ?string $cwd = null, int $timeoutSec = 3600): ProcessResult
    {
        if ($command === []) {
            throw new \InvalidArgumentException('Commande vide.');
        }

        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $opener = $this->procOpener ?? 'proc_open';
        $process = $opener($command, $descriptorSpec, $pipes, $cwd ?? null, null);
        if (!is_resource($process)) {
            throw new \RuntimeException('Impossible de démarrer le processus : ' . $command[0]);
        }

        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $stdout = '';
        $stderr = '';
        $start = time();

        while (true) {
            $stdout .= stream_get_contents($pipes[1]) ?: '';
            $stderr .= stream_get_contents($pipes[2]) ?: '';

            $status = proc_get_status($process);
            if (!$status['running']) {
                $stdout .= stream_get_contents($pipes[1]) ?: '';
                $stderr .= stream_get_contents($pipes[2]) ?: '';
                break;
            }

            if ((time() - $start) > $timeoutSec) {
                proc_terminate($process);
                fclose($pipes[1]);
                fclose($pipes[2]);
                proc_close($process);

                throw new \RuntimeException('Délai dépassé pour ' . $command[0]);
            }

            usleep(100_000);
        }

        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        return new ProcessResult($exitCode, $stdout, $stderr, $command);
    }

    /**
     * Démarre un processus détaché (Linux). Utilisé uniquement pour lancer le worker en dev.
     *
     * @param list<string> $command
     */
    public function startDetached(array $command, ?string $cwd = null): bool
    {
        if ($command === [] || PHP_OS_FAMILY === 'Windows') {
            return false;
        }

        $line = implode(' ', array_map('escapeshellarg', $command));
        $full = 'cd ' . escapeshellarg($cwd ?? getcwd() ?: '.')
            . ' && ' . $line . ' > /dev/null 2>&1 & echo $!';

        $output = [];
        $exit = 0;
        exec($full, $output, $exit);

        return $exit === 0;
    }

    public function isExecutable(string $binary): bool
    {
        if (str_contains($binary, '/') || str_contains($binary, '\\')) {
            return is_file($binary) && is_executable($binary);
        }

        $path = getenv('PATH');
        if (!is_string($path) || $path === '') {
            return false;
        }

        foreach (explode(PATH_SEPARATOR, $path) as $dir) {
            $candidate = rtrim($dir, '/') . '/' . $binary;
            if (is_file($candidate) && is_executable($candidate)) {
                return true;
            }
        }

        return false;
    }
}

final class ProcessResult
{
    /**
     * @param list<string> $command
     */
    public function __construct(
        public readonly int $exitCode,
        public readonly string $stdout,
        public readonly string $stderr,
        public readonly array $command,
    ) {
    }

    public function successful(): bool
    {
        return $this->exitCode === 0;
    }

    public function tail(int $max = 4000): string
    {
        $text = trim($this->stderr !== '' ? $this->stderr : $this->stdout);
        if (strlen($text) <= $max) {
            return $text;
        }

        return substr($text, -$max);
    }
}
