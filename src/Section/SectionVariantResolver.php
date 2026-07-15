<?php

declare(strict_types=1);

namespace Capsule\Section;

use Capsule\SectionRegistry;

final class SectionVariantResolver
{
    public function __construct(
        private readonly SectionRegistry $registry,
        private readonly SectionHandlerRegistry $handlers,
    ) {
    }

    public function resolve(string $type, string $requested): string
    {
        $variants = $this->registry->getVariants($type);
        $keys = array_map('strval', array_keys($variants));
        $handler = $this->handlers->get($type);

        if ($requested !== '' && $handler !== null) {
            $normalized = $handler->normalizeVariant($requested);
            if (in_array($normalized, $keys, true)) {
                return $normalized;
            }
        }

        if ($requested !== '' && in_array($requested, $keys, true)) {
            return $requested;
        }

        $fallback = $this->registry->getDefaultVariant($type) ?? ($keys[0] ?? 'default');
        if ($handler !== null) {
            $normalizedFallback = $handler->normalizeVariant($fallback);
            if (in_array($normalizedFallback, $keys, true)) {
                return $normalizedFallback;
            }
        }

        return $keys[0] ?? 'default';
    }
}
