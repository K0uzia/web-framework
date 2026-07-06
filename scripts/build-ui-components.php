<?php

declare(strict_types=1);

/**
 * Fusionne les composants UI (groupe ui) dans resources/sections/registry.yaml.
 *
 * Usage: php scripts/build-ui-components.php
 */

require dirname(__DIR__) . '/vendor/autoload.php';

use Capsule\YamlData;

$root = dirname(__DIR__);
$registryPath = $root . '/resources/sections/registry.yaml';
$sectionsDir = $root . '/resources/sections';
$cssDir = $root . '/public/assets/css/sections';

/** @var list<string> */
const UI_GROUP_ORDER = [
    'hero', 'feature', 'integration', 'about', 'content', 'gallery', 'pricing', 'compare',
    'cta', 'newsletter', 'testimonial', 'stats', 'logos', 'team', 'faq', 'contact',
    'blog', 'project', 'timeline', 'service', 'auth', 'career', 'compliance', 'case-study',
    'changelog', 'community', 'download', 'industry', 'list', 'experience', 'process',
    'waitlist', 'award', 'resource', 'code', 'demo', 'ui',
];

final class UiYamlWriter
{
    /**
     * @param array<string, mixed> $data
     */
    public static function dump(array $data): string
    {
        $lines = [];
        foreach ($data as $key => $value) {
            self::dumpKey($lines, (string) $key, $value, 0);
        }

        return implode("\n", $lines) . "\n";
    }

    /**
     * @param list<string> $lines
     * @param mixed $value
     */
    private static function dumpKey(array &$lines, string $key, $value, int $indent): void
    {
        $pad = str_repeat(' ', $indent);
        if (!is_array($value)) {
            $lines[] = $pad . $key . ': ' . self::scalar($value);

            return;
        }
        if ($value === []) {
            $lines[] = $pad . $key . ': []';

            return;
        }
        if (self::isList($value)) {
            $lines[] = $pad . $key . ': ' . self::inlineList($value);

            return;
        }
        $lines[] = $pad . $key . ':';
        foreach ($value as $childKey => $childValue) {
            self::dumpKey($lines, (string) $childKey, $childValue, $indent + 2);
        }
    }

    /**
     * @param array<int, mixed> $list
     */
    private static function inlineList(array $list): string
    {
        $parts = [];
        foreach ($list as $item) {
            $parts[] = self::scalar($item);
        }

        return '[' . implode(', ', $parts) . ']';
    }

    /**
     * @param array<mixed> $arr
     */
    private static function isList(array $arr): bool
    {
        if ($arr === []) {
            return true;
        }

        return array_keys($arr) === range(0, count($arr) - 1);
    }

    /**
     * @param mixed $value
     */
    private static function scalar($value): string
    {
        if (!is_string($value) && !is_int($value) && !is_float($value) && !is_bool($value)) {
            return '""';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        $s = (string) $value;
        if ($s === '' || preg_match('/[:#\[\]{}&,\*\?!|>\'"%@`]/', $s) === 1 || str_starts_with($s, ' ')) {
            return "'" . str_replace("'", "''", $s) . "'";
        }

        return $s;
    }
}

/**
 * @return array<string, mixed>
 */
function uiStyleFields(): array
{
    return [
        'bg' => ['type' => 'color-token', 'label' => 'Fond'],
        'padding' => [
            'type' => 'select',
            'label' => 'Espacement',
            'options' => ['sm', 'md', 'lg', 'xl'],
        ],
    ];
}

/**
 * @return array<string, array<string, mixed>>
 */
function buildUiRegistrySpec(): array
{
  $style = uiStyleFields();

  return [
    'ui-alert' => [
      'label' => 'Alerte',
      'group' => 'ui',
      'icon' => 'fa-solid fa-circle-info',
      'description' => 'Message d''information, de succès ou d''avertissement.',
      'variants' => [
        'info' => ['label' => 'Information', 'description' => 'Alerte informative.'],
        'success' => ['label' => 'Succès', 'description' => 'Confirmation ou succès.'],
        'warning' => ['label' => 'Avertissement', 'description' => 'Mise en garde modérée.'],
      ],
      'content_fields' => [
        'title' => ['type' => 'text', 'label' => 'Titre', 'client_editable' => true],
        'text' => ['type' => 'textarea', 'label' => 'Message', 'client_editable' => true],
      ],
      'style_fields' => $style,
    ],
    'ui-badge-row' => [
      'label' => 'Badges',
      'group' => 'ui',
      'icon' => 'fa-solid fa-tags',
      'description' => 'Rangée de badges ou étiquettes.',
      'variants' => [
        'default' => ['label' => 'Par défaut', 'description' => 'Badges rectangulaires.'],
        'pills' => ['label' => 'Pilules', 'description' => 'Badges arrondis.'],
      ],
      'content_fields' => [
        'title' => ['type' => 'text', 'label' => 'Titre optionnel', 'client_editable' => true],
        'items' => [
          'type' => 'repeater',
          'label' => 'Badges',
          'client_editable' => true,
          'fields' => ['title' => ['type' => 'text', 'label' => 'Libellé']],
        ],
      ],
      'style_fields' => $style,
    ],
    'ui-button-group' => [
      'label' => 'Groupe de boutons',
      'group' => 'ui',
      'icon' => 'fa-solid fa-hand-pointer',
      'description' => 'Actions alignées côte à côte.',
      'variants' => [
        'primary' => ['label' => 'Primaire', 'description' => 'Boutons principaux.'],
        'mixed' => ['label' => 'Mixte', 'description' => 'Primaire et secondaire.'],
      ],
      'content_fields' => [
        'buttons' => ['type' => 'buttons', 'label' => 'Boutons', 'client_editable' => true],
      ],
      'style_fields' => $style,
    ],
    'ui-card' => [
      'label' => 'Cartes',
      'group' => 'ui',
      'icon' => 'fa-solid fa-id-card',
      'description' => 'Cartes de contenu compactes.',
      'variants' => [
        'default' => ['label' => 'Par défaut', 'description' => 'Cartes avec ombre légère.'],
        'bordered' => ['label' => 'Bordure', 'description' => 'Cartes avec contour.'],
      ],
      'content_fields' => [
        'title' => ['type' => 'text', 'label' => 'Titre de section', 'client_editable' => true],
        'subtitle' => ['type' => 'textarea', 'label' => 'Sous-titre', 'client_editable' => true],
        'items' => [
          'type' => 'repeater',
          'label' => 'Cartes',
          'client_editable' => true,
          'fields' => [
            'title' => ['type' => 'text', 'label' => 'Titre'],
            'text' => ['type' => 'textarea', 'label' => 'Texte'],
          ],
        ],
      ],
      'style_fields' => $style,
    ],
    'ui-accordion' => [
      'label' => 'Accordéon',
      'group' => 'ui',
      'icon' => 'fa-solid fa-bars-staggered',
      'description' => 'Sections repliables en HTML natif.',
      'variants' => [
        'default' => ['label' => 'Par défaut', 'description' => 'Details et summary.'],
      ],
      'content_fields' => [
        'title' => ['type' => 'text', 'label' => 'Titre de section', 'client_editable' => true],
        'items' => [
          'type' => 'repeater',
          'label' => 'Panneaux',
          'client_editable' => true,
          'fields' => [
            'title' => ['type' => 'text', 'label' => 'Titre'],
            'text' => ['type' => 'textarea', 'label' => 'Contenu'],
          ],
        ],
      ],
      'style_fields' => $style,
    ],
    'ui-tabs' => [
      'label' => 'Onglets',
      'group' => 'ui',
      'icon' => 'fa-solid fa-folder-open',
      'description' => 'Navigation par onglets avec script léger.',
      'variants' => [
        'default' => ['label' => 'Par défaut', 'description' => 'Onglets horizontaux.'],
      ],
      'content_fields' => [
        'title' => ['type' => 'text', 'label' => 'Titre de section', 'client_editable' => true],
        'items' => [
          'type' => 'repeater',
          'label' => 'Onglets',
          'client_editable' => true,
          'fields' => [
            'title' => ['type' => 'text', 'label' => 'Libellé'],
            'text' => ['type' => 'textarea', 'label' => 'Contenu'],
          ],
        ],
      ],
      'style_fields' => $style,
    ],
    'ui-rating' => [
      'label' => 'Notation',
      'group' => 'ui',
      'icon' => 'fa-solid fa-star',
      'description' => 'Affichage d''étoiles et libellé.',
      'variants' => [
        'stars' => ['label' => 'Étoiles', 'description' => 'Notation visuelle.'],
      ],
      'content_fields' => [
        'title' => ['type' => 'text', 'label' => 'Libellé accessible', 'client_editable' => true],
        'stars' => ['type' => 'text', 'label' => 'Étoiles (texte)', 'client_editable' => true],
        'subtitle' => ['type' => 'text', 'label' => 'Texte complémentaire', 'client_editable' => true],
      ],
      'style_fields' => $style,
    ],
    'ui-avatar-group' => [
      'label' => 'Avatars',
      'group' => 'ui',
      'icon' => 'fa-solid fa-users',
      'description' => 'Pile d''avatars avec initiales.',
      'variants' => [
        'stack' => ['label' => 'Empilés', 'description' => 'Avatars superposés.'],
      ],
      'content_fields' => [
        'title' => ['type' => 'text', 'label' => 'Titre optionnel', 'client_editable' => true],
        'items' => [
          'type' => 'repeater',
          'label' => 'Personnes',
          'client_editable' => true,
          'fields' => ['title' => ['type' => 'text', 'label' => 'Nom']],
        ],
      ],
      'style_fields' => $style,
    ],
    'ui-marquee' => [
      'label' => 'Bande défilante',
      'group' => 'ui',
      'icon' => 'fa-solid fa-align-left',
      'description' => 'Logos ou libellés en défilement.',
      'variants' => [
        'logos' => ['label' => 'Logos', 'description' => 'Défilement horizontal.'],
      ],
      'content_fields' => [
        'title' => ['type' => 'text', 'label' => 'Titre optionnel', 'client_editable' => true],
        'items' => [
          'type' => 'repeater',
          'label' => 'Éléments',
          'client_editable' => true,
          'fields' => ['title' => ['type' => 'text', 'label' => 'Libellé']],
        ],
      ],
      'style_fields' => $style,
    ],
    'ui-announcement' => [
      'label' => 'Annonce',
      'group' => 'ui',
      'icon' => 'fa-solid fa-bullhorn',
      'description' => 'Bandeau d''annonce avec action.',
      'variants' => [
        'strip' => ['label' => 'Bandeau', 'description' => 'Bande pleine largeur.'],
      ],
      'content_fields' => [
        'text' => ['type' => 'textarea', 'label' => 'Message', 'client_editable' => true],
        'buttons' => ['type' => 'buttons', 'label' => 'Boutons', 'client_editable' => true],
      ],
      'style_fields' => $style,
    ],
    'ui-progress' => [
      'label' => 'Progression',
      'group' => 'ui',
      'icon' => 'fa-solid fa-bars-progress',
      'description' => 'Barres de progression.',
      'variants' => [
        'bars' => ['label' => 'Barres', 'description' => 'Une barre par indicateur.'],
      ],
      'content_fields' => [
        'title' => ['type' => 'text', 'label' => 'Titre de section', 'client_editable' => true],
        'items' => [
          'type' => 'repeater',
          'label' => 'Indicateurs',
          'client_editable' => true,
          'fields' => [
            'title' => ['type' => 'text', 'label' => 'Libellé'],
            'value' => ['type' => 'text', 'label' => 'Pourcentage (0-100)'],
          ],
        ],
      ],
      'style_fields' => $style,
    ],
    'ui-separator' => [
      'label' => 'Séparateur',
      'group' => 'ui',
      'icon' => 'fa-solid fa-minus',
      'description' => 'Ligne ou séparateur avec texte.',
      'variants' => [
        'line' => ['label' => 'Ligne', 'description' => 'Trait horizontal.'],
        'text' => ['label' => 'Avec texte', 'description' => 'Ligne et libellé centré.'],
      ],
      'content_fields' => [
        'title' => ['type' => 'text', 'label' => 'Texte central', 'client_editable' => true],
      ],
      'style_fields' => $style,
    ],
    'ui-social' => [
      'label' => 'Réseaux sociaux',
      'group' => 'ui',
      'icon' => 'fa-solid fa-share-nodes',
      'description' => 'Rangée d''icônes de réseaux.',
      'variants' => [
        'default' => ['label' => 'Par défaut', 'description' => 'Icônes Font Awesome.'],
      ],
      'content_fields' => [
        'title' => ['type' => 'text', 'label' => 'Libellé accessible', 'client_editable' => true],
        'items' => [
          'type' => 'repeater',
          'label' => 'Liens',
          'client_editable' => true,
          'fields' => [
            'title' => ['type' => 'text', 'label' => 'Classe icône (ex. fa-github)'],
            'text' => ['type' => 'text', 'label' => 'Nom accessible'],
            'href' => ['type' => 'text', 'label' => 'URL'],
          ],
        ],
      ],
      'style_fields' => $style,
    ],
    'ui-counter' => [
      'label' => 'Compteurs',
      'group' => 'ui',
      'icon' => 'fa-solid fa-arrow-trend-up',
      'description' => 'Chiffres animés à l''affichage.',
      'variants' => [
        'default' => ['label' => 'Par défaut', 'description' => 'Animation au scroll.'],
      ],
      'content_fields' => [
        'title' => ['type' => 'text', 'label' => 'Titre de section', 'client_editable' => true],
        'items' => [
          'type' => 'repeater',
          'label' => 'Statistiques',
          'client_editable' => true,
          'fields' => [
            'value' => ['type' => 'text', 'label' => 'Valeur'],
            'title' => ['type' => 'text', 'label' => 'Libellé'],
          ],
        ],
      ],
      'style_fields' => $style,
    ],
    'ui-code-snippet' => [
      'label' => 'Extrait de code',
      'group' => 'ui',
      'icon' => 'fa-solid fa-terminal',
      'description' => 'Bloc de code pour la documentation.',
      'variants' => [
        'block' => ['label' => 'Bloc', 'description' => 'Snippet sur fond sombre.'],
      ],
      'content_fields' => [
        'title' => ['type' => 'text', 'label' => 'Titre', 'client_editable' => true],
        'code' => ['type' => 'textarea', 'label' => 'Code', 'client_editable' => true],
      ],
      'style_fields' => $style,
    ],
  ];
}

/**
 * @param array<string, mixed> $existing
 * @param array<string, mixed> $specEntry
 * @return array<string, mixed>
 */
function mergeUiType(array $existing, array $specEntry): array
{
    $merged = $existing;
    foreach (['label', 'group', 'icon', 'description'] as $key) {
        if (isset($specEntry[$key])) {
            $merged[$key] = $specEntry[$key];
        }
    }
    if (isset($specEntry['content_fields'])) {
        $merged['content_fields'] = $specEntry['content_fields'];
    }
    if (isset($specEntry['style_fields'])) {
        $merged['style_fields'] = $specEntry['style_fields'];
    }
    $existingVariants = is_array($merged['variants'] ?? null) ? $merged['variants'] : [];
    $specVariants = is_array($specEntry['variants'] ?? null) ? $specEntry['variants'] : [];
    foreach ($specVariants as $vKey => $vDef) {
        if (!isset($existingVariants[$vKey]) || !is_array($existingVariants[$vKey])) {
            $existingVariants[$vKey] = $vDef;
            continue;
        }
        foreach (['label', 'description'] as $f) {
            if (isset($vDef[$f])) {
                $existingVariants[$vKey][$f] = $vDef[$f];
            }
        }
    }
    $merged['variants'] = $existingVariants;

    return $merged;
}

/**
 * @param array<string, mixed> $registry
 * @return array<string, mixed>
 */
function orderRegistryWithUi(array $registry): array
{
    $byGroup = [];
    foreach ($registry as $type => $def) {
        $group = is_array($def) && is_string($def['group'] ?? null) ? $def['group'] : 'content';
        $byGroup[$group][$type] = $def;
    }
    $ordered = [];
    $seenGroups = [];
    foreach (UI_GROUP_ORDER as $group) {
        if (!isset($byGroup[$group])) {
            continue;
        }
        ksort($byGroup[$group]);
        foreach ($byGroup[$group] as $type => $def) {
            $ordered[$type] = $def;
        }
        $seenGroups[$group] = true;
    }
    foreach ($byGroup as $group => $types) {
        if (isset($seenGroups[$group])) {
            continue;
        }
        ksort($types);
        foreach ($types as $type => $def) {
            $ordered[$type] = $def;
        }
    }

    return $ordered;
}

/**
 * @param array<string, mixed> $typeDef
 */
function countVariantFiles(string $type, array $typeDef, string $sectionsDir, string $cssDir): int
{
    $variants = is_array($typeDef['variants'] ?? null) ? $typeDef['variants'] : [];
    $n = 0;
    foreach (array_keys($variants) as $variant) {
        if (is_file($sectionsDir . '/' . $type . '/' . $variant . '.html')) {
            $n++;
        }
        if (is_file($cssDir . '/' . $type . '/' . $variant . '.css')) {
            $n++;
        }
    }

    return $n;
}

$existing = is_file($registryPath) ? YamlData::parse((string) file_get_contents($registryPath)) : [];
if (!is_array($existing)) {
    $existing = [];
}

$uiSpec = buildUiRegistrySpec();
$merged = $existing;
foreach ($uiSpec as $type => $specEntry) {
    $merged[$type] = mergeUiType(is_array($existing[$type] ?? null) ? $existing[$type] : [], $specEntry);
}

$registry = orderRegistryWithUi($merged);
file_put_contents($registryPath, UiYamlWriter::dump($registry));

$uiTypeCount = count($uiSpec);
$assetChecks = 0;
foreach ($uiSpec as $type => $def) {
    $assetChecks += countVariantFiles($type, $def, $sectionsDir, $cssDir);
}

echo "Composants UI fusionnés dans le registre.\n";
echo "Types UI: {$uiTypeCount}\n";
echo "Fichiers HTML/CSS présents (variantes): {$assetChecks}\n";
