<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Familles de mise en page partagées entre variantes d'un même type de bloc.
 * Évite de dupliquer des centaines de fichiers HTML/CSS (catalogue shadcnblocks).
 */
final class SectionLayoutFamilies
{
    /**
     * Modèles HTML de repli, du plus spécifique au plus générique.
     *
     * @return list<string> Noms de fichier sans extension (ex. grid-3, row).
     */
    public static function htmlFamilies(string $variant): array
    {
        $families = [];

        if ($variant === 'bento') {
            $families = ['bento'];
        } elseif (preg_match('/^feature-\d+$/', $variant) === 1) {
            $families = ['shared'];
        } elseif (preg_match('/^grid(-\d+)?$/', $variant) === 1 || $variant === 'masonry' || $variant === 'featured') {
            $families = ['grid-3', 'grid'];
        } elseif (in_array($variant, ['row', 'marquee', 'horizontal'], true)) {
            $families = ['row'];
        } elseif (str_starts_with($variant, 'cards') || $variant === 'card' || $variant === 'compact' || $variant === 'simple') {
            $families = ['cards'];
        } elseif ($variant === 'list' || str_ends_with($variant, '-list') || $variant === 'bullets' || $variant === 'numbered' || $variant === 'bars') {
            $families = ['list'];
        } elseif (str_starts_with($variant, 'centered') || $variant === 'inline') {
            $families = ['centered'];
        } elseif (str_starts_with($variant, 'split') || $variant === 'image-split') {
            $families = ['split'];
        } elseif ($variant === 'table' || str_starts_with($variant, 'table')) {
            $families = ['table'];
        } elseif ($variant === 'prose' || str_starts_with($variant, 'columns')) {
            $families = ['prose'];
        } elseif ($variant === 'strip' || $variant === 'dismissible') {
            $families = ['strip'];
        } elseif ($variant === 'banner') {
            $families = ['banner'];
        } elseif ($variant === 'vertical' || $variant === 'timeline') {
            $families = ['vertical', 'row'];
        } elseif ($variant === 'two-col') {
            $families = ['two-col', 'list'];
        } elseif ($variant === 'quote' || $variant === 'block') {
            $families = ['prose'];
        }

        return array_values(array_unique($families));
    }

    /**
     * Feuilles CSS de repli (base puis surcharges éventuelles de variante).
     *
     * @return list<string>
     */
    public static function cssFamilies(string $variant): array
    {
        return self::htmlFamilies($variant);
    }
}
