<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Lecteur best-effort du nom de famille intégré dans un fichier de police
 * TrueType/OpenType (.ttf/.otf) ou WOFF 1.0 (.woff), utilisé pour pré-remplir
 * automatiquement le nom lors d'un import. Le format WOFF2 n'est pas analysé
 * (décompression Brotli non disponible nativement en PHP) : l'appelant doit
 * alors proposer un nom de repli (ex. dérivé du nom de fichier).
 */
final class FontNameReader
{
    public static function detectFamilyName(string $binary): ?string
    {
        if (strlen($binary) < 12) {
            return null;
        }

        $signature = substr($binary, 0, 4);

        if ($signature === 'wOFF') {
            return self::fromWoff($binary);
        }

        if ($signature === "\x00\x01\x00\x00" || $signature === 'OTTO' || $signature === 'true') {
            return self::fromSfnt($binary);
        }

        return null;
    }

    private static function fromWoff(string $binary): ?string
    {
        $numTables = self::uint16($binary, 12);
        $entryOffset = 44;

        for ($i = 0; $i < $numTables; $i++) {
            $entry = substr($binary, $entryOffset, 20);
            if (strlen($entry) < 20) {
                break;
            }

            if (substr($entry, 0, 4) === 'name') {
                $offset = self::uint32($entry, 4);
                $compLength = self::uint32($entry, 8);
                $origLength = self::uint32($entry, 12);
                $raw = substr($binary, $offset, $compLength);

                $tableData = $compLength === $origLength ? $raw : @gzuncompress($raw, $origLength);
                if ($tableData === false || $tableData === null) {
                    return null;
                }

                return self::parseNameTable($tableData);
            }

            $entryOffset += 20;
        }

        return null;
    }

    private static function fromSfnt(string $binary): ?string
    {
        $numTables = self::uint16($binary, 4);
        $entryOffset = 12;

        for ($i = 0; $i < $numTables; $i++) {
            $entry = substr($binary, $entryOffset, 16);
            if (strlen($entry) < 16) {
                break;
            }

            if (substr($entry, 0, 4) === 'name') {
                $offset = self::uint32($entry, 8);
                $length = self::uint32($entry, 12);

                return self::parseNameTable(substr($binary, $offset, $length));
            }

            $entryOffset += 16;
        }

        return null;
    }

    private static function parseNameTable(string $table): ?string
    {
        if (strlen($table) < 6) {
            return null;
        }

        $count = self::uint16($table, 2);
        $stringOffset = self::uint16($table, 4);
        $recordOffset = 6;
        $candidates = [];

        for ($i = 0; $i < $count; $i++) {
            $record = substr($table, $recordOffset, 12);
            if (strlen($record) < 12) {
                break;
            }

            $platformId = self::uint16($record, 0);
            $nameId = self::uint16($record, 6);
            $length = self::uint16($record, 8);
            $offset = self::uint16($record, 10);

            if (in_array($nameId, [1, 16], true)) {
                $raw = substr($table, $stringOffset + $offset, $length);
                $decoded = self::decodeNameBytes($raw, $platformId);
                if ($decoded !== null && trim($decoded) !== '') {
                    $priority = ($nameId === 16 ? 10 : 0) + ($platformId === 3 ? 5 : ($platformId === 1 ? 1 : 0));
                    $candidates[$priority] = trim($decoded);
                }
            }

            $recordOffset += 12;
        }

        if ($candidates === []) {
            return null;
        }

        krsort($candidates);

        return reset($candidates);
    }

    private static function decodeNameBytes(string $raw, int $platformId): ?string
    {
        if ($platformId === 3 || $platformId === 0) {
            $decoded = @iconv('UTF-16BE', 'UTF-8//IGNORE', $raw);

            return $decoded !== false ? $decoded : null;
        }

        if ($platformId === 1) {
            return $raw;
        }

        return null;
    }

    private static function uint16(string $data, int $offset): int
    {
        $bytes = substr($data, $offset, 2);
        if (strlen($bytes) < 2) {
            return 0;
        }

        $value = unpack('n', $bytes);

        return $value === false ? 0 : $value[1];
    }

    private static function uint32(string $data, int $offset): int
    {
        $bytes = substr($data, $offset, 4);
        if (strlen($bytes) < 4) {
            return 0;
        }

        $value = unpack('N', $bytes);

        return $value === false ? 0 : $value[1];
    }
}
