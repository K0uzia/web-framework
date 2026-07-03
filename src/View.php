<?php

declare(strict_types=1);

namespace Capsule;

final class View
{
    public function __construct(
        private readonly string $layoutsDir,
        private readonly string $partialsDir = '',
        private readonly string $pagesDir = '',
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(string $template, array $data = []): string
    {
        $file = $this->resolveTemplate($template);
        if (!is_file($file)) {
            throw new \RuntimeException("Template not found: {$file}");
        }

        $content = file_get_contents($file);
        if ($content === false) {
            throw new \RuntimeException("Cannot read template: {$file}");
        }

        return $this->compile($content, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function renderString(string $template, array $data = []): string
    {
        return $this->compile($template, $data);
    }

    public function page(string $template, array $data = [], string $layout = 'default.html'): string
    {
        $data['content'] = $this->render($template, $data);

        return $this->render($layout, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function pageFromString(string $body, array $data = [], string $layout = 'default.html'): string
    {
        $data['content'] = $this->renderString($body, $data);

        return $this->render($layout, $data);
    }

    private function resolveTemplate(string $template): string
    {
        $name = ltrim($template, '/');
        if ($this->pagesDir !== '') {
            $page = rtrim($this->pagesDir, '/') . '/' . $name;
            if (is_file($page)) {
                return $page;
            }
        }
        if ($this->partialsDir !== '') {
            $partial = rtrim($this->partialsDir, '/') . '/' . $name;
            if (is_file($partial)) {
                return $partial;
            }
        }

        return rtrim($this->layoutsDir, '/') . '/' . $name;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function compile(string $tpl, array $data): string
    {
        $tpl = preg_replace_callback(
            '/\{\{\>\s*([a-zA-Z0-9_\/\-.]+)\s*\}\}/',
            function (array $m) use ($data) {
                return $this->render($m[1], $data);
            },
            $tpl
        ) ?? $tpl;

        $tpl = preg_replace_callback(
            '/\{\{\{\s*([a-zA-Z0-9_.]+)\s*\}\}\}/',
            function (array $m) use ($data) {
                $v = $this->get($data, $m[1]);

                return is_scalar($v) ? (string) $v : '';
            },
            $tpl
        ) ?? $tpl;

        $tpl = preg_replace_callback(
            '/\{\{\s*([a-zA-Z0-9_.]+)\s*\}\}/',
            function (array $m) use ($data) {
                $v = $this->get($data, $m[1]);
                $str = is_scalar($v) ? (string) $v : '';

                return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            },
            $tpl
        ) ?? $tpl;

        return $tpl;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function get(array $data, string $key): mixed
    {
        if (!str_contains($key, '.')) {
            return $data[$key] ?? '';
        }

        $value = $data;
        foreach (explode('.', $key) as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return '';
            }
            $value = $value[$part];
        }

        return $value;
    }
}
