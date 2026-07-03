<?php

declare(strict_types=1);

namespace Capsule;

final class SectionRegistry
{
    /** @var array<string, mixed>|null */
    private ?array $registry = null;

    public function __construct(private readonly string $registryFile)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->load();
    }

    /**
     * @return list<string>
     */
    public function getTypes(): array
    {
        return array_keys($this->load());
    }

    /**
     * @return array<string, array{label: string}>
     */
    public function getVariants(string $type): array
    {
        $def = $this->load()[$type] ?? null;
        if (!is_array($def) || !is_array($def['variants'] ?? null)) {
            return [];
        }

        /** @var array<string, array{label: string}> $variants */
        $variants = $def['variants'];

        return $variants;
    }

    /**
     * @return array<string, mixed>
     */
    public function getTypeDefinition(string $type): array
    {
        $def = $this->load()[$type] ?? null;

        return is_array($def) ? $def : [];
    }

    /**
     * @return array<string, mixed>
     */
    public function getContentFields(string $type): array
    {
        $def = $this->getTypeDefinition($type);
        $fields = $def['content_fields'] ?? [];

        return is_array($fields) ? $fields : [];
    }

    /**
     * @return array<string, mixed>
     */
    public function getStyleFields(string $type): array
    {
        $def = $this->getTypeDefinition($type);
        $fields = $def['style_fields'] ?? [];

        return is_array($fields) ? $fields : [];
    }

    /**
     * @return array<string, mixed>
     */
    public function getClientEditableFields(string $type): array
    {
        $editable = [];
        foreach ($this->getContentFields($type) as $key => $field) {
            if (!is_array($field)) {
                continue;
            }
            if (($field['client_editable'] ?? false) === true) {
                $editable[$key] = $field;
            }
        }

        return $editable;
    }

    /**
     * @return array<string, mixed>
     */
    private function load(): array
    {
        if ($this->registry !== null) {
            return $this->registry;
        }

        if (!is_file($this->registryFile)) {
            $this->registry = [];

            return $this->registry;
        }

        $raw = file_get_contents($this->registryFile);
        if ($raw === false) {
            $this->registry = [];

            return $this->registry;
        }

        $parsed = YamlData::parse($raw);
        $this->registry = $parsed;

        return $this->registry;
    }
}
