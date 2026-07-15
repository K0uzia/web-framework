<?php

declare(strict_types=1);

namespace App\Http\Dev\Sections;

use Capsule\BackgroundType;
use Capsule\ShaderLibrary;

/**
 * Sélecteur de fond : type image / vidéo / shader et galerie associée.
 */
final class SectionBackgroundFieldView
{
    /**
     * @param array<string, mixed> $content
     * @param list<string>         $imageUrls
     * @param list<string>         $videoUrls
     */
    public static function render(
        string $slug,
        string $sectionId,
        array $content,
        array $imageUrls,
        array $videoUrls,
        string $imageAccept,
        string $videoAccept,
    ): string {
        $type = BackgroundType::normalize((string) ($content['background_type'] ?? BackgroundType::IMAGE));
        $safeSectionId = htmlspecialchars($sectionId, ENT_QUOTES);

        $typeSelect = '<div class="dev-field">'
            . '<label class="dev-label" for="' . $safeSectionId . '-background-type">Type de fond</label>'
            . '<select class="dev-input dev-select" id="' . $safeSectionId . '-background-type" name="content_background_type" data-dev-background-type>';
        foreach (BackgroundType::labels() as $value => $label) {
            $selected = $value === $type ? ' selected' : '';
            $typeSelect .= '<option value="' . htmlspecialchars($value, ENT_QUOTES) . '"' . $selected . '>'
                . htmlspecialchars($label, ENT_QUOTES) . '</option>';
        }
        $typeSelect .= '</select></div>';

        $imagePanel = self::panel(
            BackgroundType::IMAGE,
            $type === BackgroundType::IMAGE,
            SectionMediaFieldView::render(
                $slug,
                $sectionId,
                'background_image_url',
                trim((string) ($content['background_image_url'] ?? '')),
                'image',
                $imageUrls,
                $imageAccept,
                $content,
            ),
        );

        $videoPanel = self::panel(
            BackgroundType::VIDEO,
            $type === BackgroundType::VIDEO,
            SectionMediaFieldView::render(
                $slug,
                $sectionId,
                'background_video_url',
                trim((string) ($content['background_video_url'] ?? '')),
                'video',
                $videoUrls,
                $videoAccept,
                $content,
            ),
        );

        $shaderPanel = self::panel(
            BackgroundType::SHADER,
            $type === BackgroundType::SHADER,
            SectionShaderFieldView::render($sectionId, $content),
        );

        return '<div class="dev-background-fields" data-dev-background-fields>'
            . '<p class="dev-label dev-background-fields__title">Fond du bloc</p>'
            . $typeSelect
            . $imagePanel
            . $videoPanel
            . $shaderPanel
            . '</div>';
    }

    private static function panel(string $kind, bool $visible, string $html): string
    {
        $hidden = $visible ? '' : ' hidden';

        return '<div class="dev-background-fields__panel" data-dev-background-panel="' . htmlspecialchars($kind, ENT_QUOTES) . '"' . $hidden . '>'
            . $html
            . '</div>';
    }
}
