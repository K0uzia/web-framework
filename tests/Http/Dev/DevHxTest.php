<?php

declare(strict_types=1);

namespace Tests\Http\Dev;

use App\Http\Dev\DevHx;
use Capsule\Http\Message\Request;
use PHPUnit\Framework\TestCase;

final class DevHxTest extends TestCase
{
    public function testDetectsHxRequestCaseInsensitively(): void
    {
        $probe = new class {
            use DevHx;

            public function probe(Request $request): bool
            {
                return $this->isHx($request);
            }
        };

        $this->assertTrue($probe->probe(new Request(
            'POST',
            '/dev/site',
            [],
            ['hx-request' => 'true'],
            [],
            [],
            rawBody: '',
        )));

        $this->assertTrue($probe->probe(new Request(
            'POST',
            '/dev/site',
            [],
            ['HX-Request' => 'TRUE'],
            [],
            [],
            rawBody: '',
        )));

        $this->assertFalse($probe->probe(new Request(
            'POST',
            '/dev/site',
            [],
            [],
            [],
            [],
            rawBody: '',
        )));
    }
}
