<?php

declare(strict_types=1);

namespace Tests\Http\Dev;

use App\Http\Dev\Sections\ClientAccessKinds;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ClientAccessKinds::class)]
final class ClientAccessKindsTest extends TestCase
{
    public function testGroupFieldKeysByKind(): void
    {
        $fields = [
            'title' => ['type' => 'text', 'label' => 'Titre', 'client_editable' => true],
            'subtitle' => ['type' => 'textarea', 'label' => 'Desc', 'client_editable' => 'true'],
            'image_url' => ['type' => 'image', 'label' => 'Image', 'client_editable' => true],
            'buttons' => ['type' => 'buttons', 'label' => 'Boutons', 'client_editable' => true],
            'badge' => ['type' => 'text', 'label' => 'Badge', 'client_editable' => false],
        ];

        $groups = ClientAccessKinds::groupFieldKeys($fields);

        $this->assertSame(['title', 'subtitle'], $groups['text']);
        $this->assertSame(['image_url'], $groups['image']);
        $this->assertSame(['buttons'], $groups['link']);
    }

    public function testRepeaterItemsJoinTextAndImageGroups(): void
    {
        $fields = [
            'title' => ['type' => 'text', 'label' => 'Titre', 'client_editable' => true],
            'items' => [
                'type' => 'repeater',
                'label' => 'Éléments',
                'fields' => [
                    'title' => ['type' => 'text', 'label' => 'Titre'],
                    'text' => ['type' => 'textarea', 'label' => 'Description'],
                    'url' => ['type' => 'image', 'label' => 'Image'],
                    'href' => ['type' => 'text', 'label' => 'Lien'],
                ],
            ],
        ];

        $groups = ClientAccessKinds::groupFieldKeys($fields);

        $this->assertContains('items', $groups['text']);
        $this->assertContains('items', $groups['image']);
        $this->assertContains('items', $groups['link']);
        $this->assertContains('title', $groups['text']);
    }

    public function testResolveAllowedFieldsAddsMissingItemsList(): void
    {
        $fields = [
            'title' => ['type' => 'text', 'label' => 'Titre', 'client_editable' => true],
            'subtitle' => ['type' => 'textarea', 'label' => 'Desc', 'client_editable' => true],
            'items' => [
                'type' => 'repeater',
                'label' => 'Éléments',
                'fields' => [
                    'title' => ['type' => 'text', 'label' => 'Titre'],
                    'text' => ['type' => 'textarea', 'label' => 'Description'],
                ],
            ],
        ];

        // Ancienne config : texte ouvert, mais pas la liste items.
        $resolved = ClientAccessKinds::resolveAllowedFields($fields, ['subtitle', 'title']);

        $this->assertContains('items', $resolved);
        $this->assertContains('title', $resolved);
        $this->assertContains('subtitle', $resolved);
        $this->assertSame(['items', 'subtitle', 'title'], $resolved);
    }

    public function testRoundTripPermissions(): void
    {
        $groups = [
            'text' => ['title', 'subtitle'],
            'image' => ['image_url'],
            'link' => ['buttons'],
        ];

        $allowed = ClientAccessKinds::allowedFromPermissions($groups, [
            'editableText' => true,
            'editableImage' => false,
            'editableLink' => true,
        ]);

        $this->assertSame(['buttons', 'subtitle', 'title'], $allowed);

        $perms = ClientAccessKinds::permissionsFromAllowed($groups, $allowed);
        $this->assertTrue($perms['editableText']);
        $this->assertFalse($perms['editableImage']);
        $this->assertTrue($perms['editableLink']);
    }
}
