<?php

declare(strict_types=1);

namespace Tests\Http\Admin;

use App\Http\Admin\RelativeTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RelativeTime::class)]
final class RelativeTimeTest extends TestCase
{
    public function testFormatReturnsInstantForRecentTimestamp(): void
    {
        $now = strtotime('2026-07-17 10:00:00 UTC');
        $value = gmdate('Y-m-d H:i:s', $now - 30);

        $this->assertSame('à l\'instant', RelativeTime::format($value, $now));
    }

    public function testFormatReturnsDaysAgo(): void
    {
        $now = strtotime('2026-07-17 10:00:00 UTC');
        $value = gmdate('Y-m-d H:i:s', $now - 2 * 86400);

        $this->assertSame('il y a 2 jours', RelativeTime::format($value, $now));
    }

    public function testAbsoluteFormatsDate(): void
    {
        $this->assertSame('17/07/2026 à 10:00', RelativeTime::absolute('2026-07-17 10:00:00'));
    }

    public function testEmptyValueReturnsDash(): void
    {
        $this->assertSame('-', RelativeTime::format(''));
        $this->assertSame('-', RelativeTime::absolute(''));
    }
}
