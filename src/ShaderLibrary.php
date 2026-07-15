<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Presets shader pour les fonds animés (portage de shader3 shadcnblocks).
 */
final class ShaderLibrary
{
    public const DEFAULT_ID = 'shader3-default';

    /**
     * @return list<array{id: string, name: string, description: string, color: string, preview: string}>
     */
    public static function presets(): array
    {
        return [
            [
                'id' => 'shader3-default',
                'name' => 'Bulles menthe',
                'description' => 'Effet verre irisé avec bulles animées.',
                'color' => '#bbffcc',
                'preview' => 'radial-gradient(circle at 35% 40%, #bbffcc 0%, #1e293b 72%)',
            ],
            [
                'id' => 'shader3-sky',
                'name' => 'Bulles ciel',
                'description' => 'Reflets bleus et bulles lumineuses.',
                'color' => '#7dd3fc',
                'preview' => 'radial-gradient(circle at 40% 35%, #7dd3fc 0%, #0f172a 70%)',
            ],
            [
                'id' => 'shader3-lavender',
                'name' => 'Bulles lavande',
                'description' => 'Teintes violettes et reflets doux.',
                'color' => '#c4b5fd',
                'preview' => 'radial-gradient(circle at 30% 45%, #c4b5fd 0%, #1e1b4b 72%)',
            ],
            [
                'id' => 'shader3-rose',
                'name' => 'Bulles rose',
                'description' => 'Ambiance chaleureuse et irisée.',
                'color' => '#fda4af',
                'preview' => 'radial-gradient(circle at 38% 42%, #fda4af 0%, #3f1d2b 72%)',
            ],
            [
                'id' => 'shader3-amber',
                'name' => 'Bulles ambre',
                'description' => 'Lueurs dorées sur fond sombre.',
                'color' => '#fcd34d',
                'preview' => 'radial-gradient(circle at 42% 38%, #fcd34d 0%, #422006 72%)',
            ],
        ];
    }

    public static function normalizeId(string $id): string
    {
        $id = trim($id);
        foreach (self::presets() as $preset) {
            if (($preset['id'] ?? '') === $id) {
                return $id;
            }
        }

        return self::DEFAULT_ID;
    }

    /**
     * @return array{id: string, name: string, description: string, color: string, preview: string}|null
     */
    public static function find(string $id): ?array
    {
        $id = self::normalizeId($id);
        foreach (self::presets() as $preset) {
            if (($preset['id'] ?? '') === $id) {
                return $preset;
            }
        }

        return null;
    }

    public static function colorFor(string $id, string $fallback = '#bbffcc'): string
    {
        $preset = self::find($id);

        return $preset !== null ? (string) ($preset['color'] ?? $fallback) : $fallback;
    }

    public static function normalizeColor(string $color, string $fallback = '#bbffcc'): string
    {
        $color = trim($color);
        if (preg_match('/^#[0-9a-fA-F]{6}$/', $color) === 1) {
            return strtolower($color);
        }

        return strtolower($fallback);
    }

    /**
     * Fond sombre derrière le canvas WebGL (fallback CSS, indépendant de la couleur accent).
     */
    public static function sceneBackgroundFor(string $id): string
    {
        return match (self::normalizeId($id)) {
            'shader3-sky' => '#0f172a',
            'shader3-lavender' => '#1e1b4b',
            'shader3-rose' => '#3f1d2b',
            'shader3-amber' => '#422006',
            default => '#1e293b',
        };
    }
}
