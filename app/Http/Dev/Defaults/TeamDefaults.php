<?php

declare(strict_types=1);

namespace App\Http\Dev\Defaults;

use Capsule\SectionAssets;

trait TeamDefaults
{
    private static function teamContent(string $variant): array
    {
        return [
            'title' => 'Équipe',
            'subtitle' => 'Notre équipe réunit des expertises complémentaires en design, ingénierie et développement produit.',
            'items' => self::teamItems($variant === 'team2'),
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    private static function teamItems(bool $withSocial): array
    {
        $avatars = [
            SectionAssets::shared(self::SHARED, 'avatars-webp/avatar-1.webp'),
            SectionAssets::shared(self::SHARED, 'avatars-webp/avatar-3.webp'),
            SectionAssets::shared(self::SHARED, 'avatars-webp/avatar-4.webp'),
            SectionAssets::shared(self::SHARED, 'avatars-webp/avatar-6.webp'),
            SectionAssets::shared(self::SHARED, 'avatars/avatar1.jpg'),
            SectionAssets::shared(self::SHARED, 'avatars/avatar2.jpg'),
        ];
        $members = [
            ['title' => 'Sarah Chen', 'label' => 'PDG et fondatrice'],
            ['title' => 'Marcus Rodriguez', 'label' => 'Directeur technique'],
            ['title' => 'Emily Watson', 'label' => 'Directrice design'],
            ['title' => 'David Kim', 'label' => 'Lead ingénieur'],
            ['title' => 'Lisa Thompson', 'label' => 'Chef de produit'],
            ['title' => 'Alex Johnson', 'label' => 'Designer UX'],
        ];
        $items = [];
        foreach ($members as $index => $member) {
            $item = [
                'title' => $member['title'],
                'label' => $member['label'],
                'url' => $avatars[$index % count($avatars)],
            ];
            if ($withSocial) {
                $item['github'] = '#';
                $item['twitter'] = '#';
                $item['linkedin'] = '#';
            }
            $items[] = $item;
        }

        return $items;
    }

    /**
     * @return array<string, mixed>
     */}
