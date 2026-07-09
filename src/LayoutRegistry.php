<?php

declare(strict_types=1);

namespace Capsule;

final class LayoutRegistry
{
    public function __construct(private readonly string $layoutsDir)
    {
    }

    /**
     * @return list<string>
     */
    public function all(): array
    {
        $files = glob(rtrim($this->layoutsDir, '/') . '/*.html') ?: [];
        $layouts = [];

        foreach ($files as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            if ($name !== '') {
                $layouts[] = $name;
            }
        }

        sort($layouts);

        return $layouts;
    }

    public function exists(string $layout): bool
    {
        $safe = preg_replace('/[^a-zA-Z0-9_-]/', '', $layout) ?? '';

        return $safe !== '' && is_file(rtrim($this->layoutsDir, '/') . '/' . $safe . '.html');
    }
}
