<?php

declare(strict_types=1);

namespace Capsule;

use Capsule\Section\SectionHandlerRegistry;

/**
 * Résout les scripts JS à charger pour une page rendue.
 */
final class ScriptResolver
{
    /** @var array<string, list<string>> */
    private const VARIANT_SCRIPTS = [
        'ui-tabs' => ['sections/ui-tabs.js'],
        'ui-marquee' => ['sections/ui-marquee.js'],
        'ui-counter' => ['sections/ui-counter.js'],
    ];

    private readonly ?SectionHandlerRegistry $handlers;

    public function __construct(
        private readonly string $publicJsDir,
        private readonly string $urlPrefix = '/assets/js',
        ?SectionHandlerRegistry $handlers = null,
    ) {
        $this->handlers = $handlers;
    }

    /**
     * @param list<array{type: string, variant: string}> $sectionRefs
     *
     * @return list<string> Chemins publics (/assets/js/…)
     */
    public function resolve(string $pageBody, array $sectionRefs = []): array
    {
        $candidates = [
            'site-nav.js',
            'site-header-blocks.js',
        ];

        $typesSeen = [];
        foreach ($sectionRefs as $ref) {
            $type = $this->safeName($ref['type'] ?? '');
            $variant = $this->safeName($ref['variant'] ?? 'default');
            if ($type === '') {
                continue;
            }

            if (!isset($typesSeen[$type])) {
                $typesSeen[$type] = true;
                foreach ($this->scriptsForType($type) as $script) {
                    $this->push($candidates, $script);
                }
            }

            foreach (self::VARIANT_SCRIPTS[$variant] ?? [] as $script) {
                $this->push($candidates, $script);
            }
        }

        if (str_contains($pageBody, 'data-hero-video')) {
            $this->push($candidates, 'sections/hero-video.js');
        }

        $existing = [];
        foreach ($candidates as $relative) {
            $file = $this->publicJsDir . '/' . $relative;
            if (is_file($file) && !in_array($relative, $existing, true)) {
                $existing[] = $relative;
            }
        }

        $prefix = rtrim($this->urlPrefix, '/');

        return array_map(
            static fn (string $relative): string => $prefix . '/' . $relative,
            $existing,
        );
    }

    /**
     * @param list<string> $srcs
     */
    public function toHtml(array $srcs, string $assetRoot = ''): string
    {
        if ($srcs === []) {
            return '';
        }

        $root = rtrim($assetRoot, '/');
        $lines = array_map(
            static function (string $src) use ($root): string {
                $href = $src;
                if ($root !== '' && str_starts_with($src, '/assets/')) {
                    $href = $root . $src;
                }

                return '    <script src="' . htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" defer></script>';
            },
            $srcs,
        );

        return implode("\n", $lines) . "\n";
    }

    /**
     * @param list<string> $list
     */
    private function push(array &$list, string $relative): void
    {
        $list[] = str_replace('\\', '/', $relative);
    }

    /**
     * @return list<string>
     */
    private function scriptsForType(string $type): array
    {
        $handlers = $this->handlers ?? new SectionHandlerRegistry();
        $handler = $handlers->get($type);
        if ($handler !== null) {
            return $handler->jsModules('default');
        }

        return [];
    }

    private function safeName(string $name): string
    {
        $base = pathinfo($name, PATHINFO_FILENAME);
        $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '', $base) ?? '';

        return $sanitized !== '' ? $sanitized : 'default';
    }
}
