<?php

declare(strict_types=1);

namespace Tests\Section;

use App\Http\Dev\Sections\SectionDefaults;
use Capsule\Section\SectionFieldSchema;
use Capsule\SectionRegistry;
use PHPUnit\Framework\TestCase;

final class DefaultsCoverageTest extends TestCase
{
    public function testDefaultContentCoversSchemaFieldsForPilotTypes(): void
    {
        $root = dirname(__DIR__, 2);
        $registry = new SectionRegistry(
            $root . '/resources/sections/registry.yaml',
            $root . '/resources/sections/_shared/style-fields.yaml',
        );
        $schema = new SectionFieldSchema($registry);

        foreach (['contact', 'features'] as $type) {
            $defaultVariant = $registry->getDefaultVariant($type)
                ?? array_key_first($registry->getVariants($type));
            $this->assertIsString($defaultVariant);

            $fields = $schema->contentFieldsForVariant($type, $defaultVariant);
            $defaults = SectionDefaults::content($type, $defaultVariant);

            foreach (array_keys($fields) as $key) {
                $fieldType = $fields[$key]['type'] ?? '';
                if (in_array($fieldType, ['repeater', 'buttons', 'image', 'video'], true)) {
                    continue;
                }
                $this->assertArrayHasKey(
                    $key,
                    $defaults,
                    "Default manquant pour {$type}.{$key} (variante {$defaultVariant})",
                );
            }
        }
    }
}
