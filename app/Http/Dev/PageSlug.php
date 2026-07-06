<?php

declare(strict_types=1);

namespace App\Http\Dev;

/**
 * Normalisation et validation des adresses de pages publiques.
 */
final class PageSlug
{
    public static function normalize(string $slug): string
    {
        return strtolower(trim($slug, " \t\n\r\0\x0B/"));
    }

    public static function fromTitle(string $title): string
    {
        $slug = mb_strtolower(trim($title));
        $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $slug);
        if (is_string($transliterated) && $transliterated !== '') {
            $slug = $transliterated;
        }

        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        $slug = trim($slug, '-');

        return $slug;
    }

    /**
     * @return string|null Message d'erreur ou null si valide.
     */
    public static function validate(string $slug): ?string
    {
        if ($slug === '') {
            return null;
        }

        if (preg_match('/^\d+$/', $slug) === 1) {
            return 'Adresse invalide : un nombre seul n\'est pas autorisé. Utilisez des lettres, par exemple « test-accueil ».';
        }

        if (!preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $slug)) {
            return 'Adresse invalide : lettres minuscules, chiffres et tirets uniquement.';
        }

        return null;
    }
}
