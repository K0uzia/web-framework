<?php

declare(strict_types=1);

namespace Capsule;

final class PageRoute
{
    /**
     * @param list<string> $paramNames
     */
    public function __construct(
        public readonly string $file,
        public readonly string $pattern,
        public readonly array $paramNames = [],
    ) {
    }

    /**
     * @return array<string, string>|null
     */
    public function match(string $path): ?array
    {
        if ($this->paramNames === []) {
            return [];
        }

        if (!preg_match($this->pattern, $path, $matches)) {
            return null;
        }

        $params = [];
        foreach ($this->paramNames as $name) {
            $params[$name] = $matches[$name] ?? '';
        }

        return $params;
    }
}
