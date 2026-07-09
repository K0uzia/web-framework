<?php

declare(strict_types=1);

namespace Tests;

use Capsule\FontNameReader;
use PHPUnit\Framework\TestCase;

final class FontNameReaderTest extends TestCase
{
    public function testDetectsFamilyNameFromWindowsUnicodeSfntRecord(): void
    {
        $binary = $this->buildSfnt('Brand Sans', 3, 1);

        $this->assertSame('Brand Sans', FontNameReader::detectFamilyName($binary));
    }

    public function testDetectsFamilyNameFromMacintoshSfntRecord(): void
    {
        $binary = $this->buildSfnt('Brand Serif', 1, 0);

        $this->assertSame('Brand Serif', FontNameReader::detectFamilyName($binary));
    }

    public function testDetectsFamilyNameFromUncompressedWoffTable(): void
    {
        $nameTable = $this->buildNameTable('Brand Woff', 3, 1);
        $binary = $this->buildWoff($nameTable, false);

        $this->assertSame('Brand Woff', FontNameReader::detectFamilyName($binary));
    }

    public function testDetectsFamilyNameFromCompressedWoffTable(): void
    {
        $nameTable = $this->buildNameTable('Brand Compressed', 3, 1);
        $binary = $this->buildWoff($nameTable, true);

        $this->assertSame('Brand Compressed', FontNameReader::detectFamilyName($binary));
    }

    public function testReturnsNullForUnrecognizedSignature(): void
    {
        $this->assertNull(FontNameReader::detectFamilyName('not a font file'));
    }

    public function testReturnsNullForTruncatedInput(): void
    {
        $this->assertNull(FontNameReader::detectFamilyName('xx'));
    }

    private function buildNameTable(string $name, int $platformId, int $encodingId): string
    {
        $encoded = $platformId === 3
            ? mb_convert_encoding($name, 'UTF-16BE', 'UTF-8')
            : $name;

        $stringOffset = 6 + 12;
        $table = pack('n3', 0, 1, $stringOffset);
        $table .= pack('n6', $platformId, $encodingId, 0, 1, strlen($encoded), 0);
        $table .= $encoded;

        return $table;
    }

    private function buildSfnt(string $name, int $platformId, int $encodingId): string
    {
        $nameTable = $this->buildNameTable($name, $platformId, $encodingId);
        $tableOffset = 12 + 16;

        $header = "\x00\x01\x00\x00" . pack('n4', 1, 0, 0, 0);
        $record = 'name' . pack('N3', 0, $tableOffset, strlen($nameTable));

        return $header . $record . $nameTable;
    }

    private function buildWoff(string $nameTable, bool $compress): string
    {
        $origLength = strlen($nameTable);
        $tableData = $compress ? (string) gzcompress($nameTable) : $nameTable;
        $compLength = strlen($tableData);

        $header = 'wOFF'
            . "\x00\x01\x00\x00"
            . pack('N', 0)
            . pack('n', 1)
            . pack('n', 0)
            . pack('N', 0)
            . pack('n', 0)
            . pack('n', 0)
            . pack('N', 0)
            . pack('N', 0)
            . pack('N', 0)
            . pack('N', 0)
            . pack('N', 0);

        $entry = 'name' . pack('N', 64) . pack('N', $compLength) . pack('N', $origLength) . pack('N', 0);

        return $header . $entry . $tableData;
    }
}
