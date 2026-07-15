<?php

declare(strict_types=1);

namespace Capsule;

final class SectionRegistry
{
    /** @var array<string, string> */
    private const FALLBACK_DEFAULT_VARIANTS = [
        'hero' => 'hero3',
        'features' => 'feature3',
        'integrations' => 'integration3',
        'pricing' => 'pricing2',
        'rate-card' => 'rate-card2',
        'contact' => 'contact2',
        'testimonials' => 'testimonial4',
        'gallery' => 'gallery4',
        'blog' => 'blog7',
        'changelog' => 'changelog1',
        'process' => 'process1',
        'list' => 'list2',
        'industry' => 'industries1',
        'download' => 'download1',
        'team' => 'team1',
        'projects' => 'projects5',
        'timeline' => 'timeline3',
    ];

    /** @var array<string, mixed>|null */
    private ?array $registry = null;

    public function __construct(
        private readonly string $registryFile,
        private readonly string $sharedStyleFieldsFile = '',
    ) {
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

    public function getDefaultVariant(string $type): ?string
    {
        $def = $this->getTypeDefinition($type);
        $default = $def['default_variant'] ?? null;

        if (is_string($default) && $default !== '') {
            return $default;
        }

        return self::FALLBACK_DEFAULT_VARIANTS[$type] ?? null;
    }

    public function isVisibleInPagePicker(string $type): bool
    {
        $def = $this->getTypeDefinition($type);

        return ($def['page_picker'] ?? true) !== false;
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

    /** @var list<string> */
    private const GROUP_ORDER = [
        'hero',
        'feature',
        'integration',
        'about',
        'content',
        'gallery',
        'pricing',
        'rate-card',
        'compare',
        'cta',
        'newsletter',
        'testimonial',
        'stats',
        'logos',
        'team',
        'faq',
        'contact',
        'blog',
        'project',
        'timeline',
        'service',
        'auth',
        'career',
        'compliance',
        'case-study',
        'changelog',
        'community',
        'download',
        'industry',
        'list',
        'experience',
        'process',
        'waitlist',
        'award',
        'resource',
        'code',
        'demo',
        'ui',
    ];

    /**
     * @return list<string>
     */
    public function getGroups(): array
    {
        $present = [];
        foreach ($this->getTypes() as $type) {
            $group = $this->getGroup($type);
            $present[$group] = true;
        }

        $ordered = [];
        foreach (self::GROUP_ORDER as $group) {
            if (isset($present[$group])) {
                $ordered[] = $group;
            }
        }
        foreach (array_keys($present) as $group) {
            if (!in_array($group, $ordered, true)) {
                $ordered[] = $group;
            }
        }

        return $ordered;
    }

    public function getGroup(string $type): string
    {
        $def = $this->getTypeDefinition($type);
        $group = $def['group'] ?? 'content';

        return is_string($group) && $group !== '' ? $group : 'content';
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
        $this->registry = $this->applySharedStyleFields($parsed);

        return $this->registry;
    }

    /**
     * @param array<string, mixed> $registry
     *
     * @return array<string, mixed>
     */
    private function applySharedStyleFields(array $registry): array
    {
        $shared = $this->loadSharedStyleFields();
        if ($shared === []) {
            return $registry;
        }

        foreach ($registry as $type => &$def) {
            if (!is_array($def)) {
                continue;
            }
            $typeFields = is_array($def['style_fields'] ?? null) ? $def['style_fields'] : [];
            $def['style_fields'] = array_merge($shared, $typeFields);
        }
        unset($def);

        return $registry;
    }

    /**
     * @return array<string, mixed>
     */
    private function loadSharedStyleFields(): array
    {
        if ($this->sharedStyleFieldsFile === '' || !is_file($this->sharedStyleFieldsFile)) {
            return [];
        }

        return YamlData::loadFile($this->sharedStyleFieldsFile);
    }
}
