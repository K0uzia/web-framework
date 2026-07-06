<?php

declare(strict_types=1);

namespace Tests\Http\Dev;

use App\Http\Dev\PageSlug;
use PHPUnit\Framework\TestCase;

final class PageSlugTest extends TestCase
{
    public function testRejectsNumericOnlySlug(): void
    {
        $this->assertSame(
            'Adresse invalide : un nombre seul n\'est pas autorisé. Utilisez des lettres, par exemple « test-accueil ».',
            PageSlug::validate('1'),
        );
    }

    public function testAcceptsValidSlug(): void
    {
        $this->assertNull(PageSlug::validate('test-accueil'));
        $this->assertNull(PageSlug::validate(''));
    }

    public function testFromTitleSlugifiesAccents(): void
    {
        $slug = PageSlug::fromTitle('Test Accueil');
        $this->assertSame('test-accueil', $slug);
    }
}
