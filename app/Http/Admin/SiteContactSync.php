<?php

declare(strict_types=1);

namespace App\Http\Admin;

use Capsule\Page;
use Capsule\PageRepository;

/**
 * Relie les coordonnées site aux blocs contact des pages.
 */
final class SiteContactSync
{
    public function __construct(
        private readonly PageRepository $pages,
    ) {
    }

    /**
     * @return array{email: string, phone: string, address: string}
     */
    public function seedFromPages(string $email, string $phone, string $address): array
    {
        if ($email !== '' || $phone !== '' || $address !== '') {
            return ['email' => $email, 'phone' => $phone, 'address' => $address];
        }

        foreach ($this->pages->all() as $page) {
            foreach ($page->sections as $section) {
                if (!is_array($section) || ($section['type'] ?? '') !== 'contact') {
                    continue;
                }
                $content = is_array($section['content'] ?? null) ? $section['content'] : [];
                $foundEmail = trim((string) ($content['email'] ?? ''));
                $foundPhone = trim((string) ($content['phone'] ?? ''));
                $foundAddress = trim((string) ($content['office_address'] ?? ''));
                if ($foundEmail === '' && $foundPhone === '' && $foundAddress === '') {
                    continue;
                }

                return [
                    'email' => $foundEmail,
                    'phone' => $foundPhone,
                    'address' => $foundAddress,
                ];
            }
        }

        return ['email' => '', 'phone' => '', 'address' => ''];
    }

    public function propagateToContactSections(string $email, string $phone, string $address): void
    {
        foreach ($this->pages->all() as $page) {
            $sections = $page->sections;
            $changed = false;
            foreach ($sections as $i => $section) {
                if (!is_array($section) || ($section['type'] ?? '') !== 'contact') {
                    continue;
                }
                $content = is_array($section['content'] ?? null) ? $section['content'] : [];
                $next = $content;
                $next['email'] = $email;
                $next['phone'] = $phone;
                $next['office_address'] = $address;
                if ($next === $content) {
                    continue;
                }
                $sections[$i]['content'] = $next;
                $changed = true;
            }
            if (!$changed) {
                continue;
            }
            $this->pages->save(new Page(
                slug: $page->slug,
                title: $page->title,
                layout: $page->layout,
                description: $page->description,
                sections: $sections,
                meta: $page->meta,
                published: $page->published,
                updatedAt: $page->updatedAt,
            ));
        }
    }
}
