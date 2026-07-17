<?php

declare(strict_types=1);

namespace App\Http\Dev\Sections;

/**
 * Mappe les champs content du registry vers des kinds Accès Client
 * (text | image | link), aligné sur block ui/src/builder/types.ts.
 *
 * Les listes (repeater / items) sont rattachées aux kinds de leurs sous-champs
 * pour que le contenu visible des cartes soit bien éditable dans /admin.
 */
final class ClientAccessKinds
{
    public const KIND_TEXT = 'text';
    public const KIND_IMAGE = 'image';
    public const KIND_LINK = 'link';

    /**
     * @param array<string, mixed> $fields content_fields filtrés variante
     *
     * @return array{text: list<string>, image: list<string>, link: list<string>}
     */
    public static function groupFieldKeys(array $fields): array
    {
        $groups = [
            self::KIND_TEXT => [],
            self::KIND_IMAGE => [],
            self::KIND_LINK => [],
        ];

        foreach ($fields as $key => $def) {
            if (!is_string($key) || $key === '' || !is_array($def)) {
                continue;
            }
            if (!self::isClientEditableField($def)) {
                continue;
            }

            if ((string) ($def['type'] ?? '') === 'repeater') {
                foreach (self::kindsForRepeater($def) as $kind) {
                    $groups[$kind][] = $key;
                }
                continue;
            }

            $kind = self::kindFor($key, $def);
            if ($kind === null) {
                continue;
            }
            $groups[$kind][] = $key;
        }

        foreach ($groups as $kind => $keys) {
            $groups[$kind] = array_values(array_unique($keys));
        }

        return $groups;
    }

    /**
     * Complète une config stockée en y ajoutant les listes (items) manquantes
     * lorsque le kind correspondant (texte / image / lien) est déjà ouvert.
     * Ne réouvre pas tous les champs du même kind.
     *
     * @param array<string, mixed> $fields
     * @param list<string>         $storedAllowed
     *
     * @return list<string>
     */
    public static function resolveAllowedFields(array $fields, array $storedAllowed): array
    {
        if ($storedAllowed === []) {
            return [];
        }

        $groups = self::groupFieldKeys($fields);
        $set = array_fill_keys($storedAllowed, true);
        $out = $set;

        foreach ($fields as $key => $def) {
            if (!is_string($key) || $key === '' || !is_array($def)) {
                continue;
            }
            if ((string) ($def['type'] ?? '') !== 'repeater' || !self::isClientEditableField($def)) {
                continue;
            }
            if (isset($out[$key])) {
                continue;
            }
            foreach (self::kindsForRepeater($def) as $kind) {
                if (self::anyAllowed($groups[$kind] ?? [], $set)) {
                    $out[$key] = true;
                    break;
                }
            }
        }

        $keys = array_keys($out);
        sort($keys);

        return $keys;
    }

    /**
     * @param array{text: list<string>, image: list<string>, link: list<string>} $groups
     * @param list<string>                                                       $allowed
     *
     * @return array{editableText: bool, editableImage: bool, editableLink: bool}
     */
    public static function permissionsFromAllowed(array $groups, array $allowed): array
    {
        $set = array_fill_keys($allowed, true);

        return [
            'editableText' => self::anyAllowed($groups[self::KIND_TEXT], $set),
            'editableImage' => self::anyAllowed($groups[self::KIND_IMAGE], $set),
            'editableLink' => self::anyAllowed($groups[self::KIND_LINK], $set),
        ];
    }

    /**
     * @param array{text: list<string>, image: list<string>, link: list<string>} $groups
     * @param array{editableText?: bool, editableImage?: bool, editableLink?: bool} $perms
     *
     * @return list<string>
     */
    public static function allowedFromPermissions(array $groups, array $perms): array
    {
        $out = [];
        if (($perms['editableText'] ?? false) === true) {
            foreach ($groups[self::KIND_TEXT] as $key) {
                $out[$key] = true;
            }
        }
        if (($perms['editableImage'] ?? false) === true) {
            foreach ($groups[self::KIND_IMAGE] as $key) {
                $out[$key] = true;
            }
        }
        if (($perms['editableLink'] ?? false) === true) {
            foreach ($groups[self::KIND_LINK] as $key) {
                $out[$key] = true;
            }
        }

        $keys = array_keys($out);
        sort($keys);

        return $keys;
    }

    /**
     * @param array<string, mixed> $def
     */
    public static function kindFor(string $key, array $def): ?string
    {
        $type = (string) ($def['type'] ?? 'text');

        if ($type === 'repeater') {
            return null;
        }
        if ($type === 'image' || $type === 'video') {
            return self::KIND_IMAGE;
        }
        if ($type === 'url' || $type === 'buttons' || str_contains($key, 'href') || str_contains($key, 'link')) {
            return self::KIND_LINK;
        }
        if ($type === 'text' || $type === 'textarea') {
            return self::KIND_TEXT;
        }

        return null;
    }

    /**
     * @param array<string, mixed> $def
     *
     * @return list<string>
     */
    private static function kindsForRepeater(array $def): array
    {
        $kinds = [];
        $subFields = is_array($def['fields'] ?? null) ? $def['fields'] : [];
        foreach ($subFields as $subKey => $subDef) {
            if (!is_string($subKey) || !is_array($subDef)) {
                continue;
            }
            $kind = self::kindFor($subKey, $subDef);
            if ($kind !== null) {
                $kinds[$kind] = true;
            }
        }

        return array_keys($kinds);
    }

    /**
     * @param array<string, mixed> $def
     */
    private static function isClientEditableField(array $def): bool
    {
        if (array_key_exists('client_editable', $def)) {
            return self::isTruthy($def['client_editable']);
        }

        // Listes d'éléments (items, etc.) : éditables par défaut pour le client.
        return (string) ($def['type'] ?? '') === 'repeater';
    }

    /**
     * @param list<string>        $keys
     * @param array<string, true> $set
     */
    private static function anyAllowed(array $keys, array $set): bool
    {
        foreach ($keys as $key) {
            if (isset($set[$key])) {
                return true;
            }
        }

        return false;
    }

    private static function isTruthy(mixed $value): bool
    {
        if ($value === true || $value === 1) {
            return true;
        }
        if (!is_string($value)) {
            return false;
        }

        return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], true);
    }
}
