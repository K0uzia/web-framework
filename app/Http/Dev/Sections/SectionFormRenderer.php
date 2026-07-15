<?php

declare(strict_types=1);

namespace App\Http\Dev\Sections;

use App\Http\Dev\LibraryMediaUploader;
use App\Http\Dev\LinkPicker;
use App\Http\Dev\SlugCodec;
use App\Http\Dev\Sections\SectionDefaults;
use Capsule\FontAwesomeIcon;
use Capsule\HeroStyle;
use Capsule\MediaDisplaySettings;
use Capsule\MediaLibrary;
use Capsule\Page;
use Capsule\PageRepository;
use Capsule\Section\SectionFieldSchema;
use Capsule\Section\SectionVariantResolver;
use Capsule\SectionRegistry;
use Capsule\ThemeColor;

final class SectionFormRenderer
{
    private const FALLBACK_ICON = 'fa-solid fa-square';

    /** @var list<string>|null */
    private ?array $renderImageUrls = null;

    /** @var list<string>|null */
    private ?array $renderVideoUrls = null;

    private ?string $renderLinkPickerOptions = null;

    public function __construct(
        private readonly SectionRegistry $registry,
        private readonly PageRepository $pages,
        private readonly MediaLibrary $mediaLibrary,
        private readonly LibraryMediaUploader $libraryUploader,
        private readonly SectionFieldSchema $fieldSchema,
        private readonly SectionVariantResolver $variantResolver,
    ) {
    }

    public function renderAll(Page $page): string
    {
        $this->renderImageUrls = $this->mediaLibrary->availableImageUrls();
        $this->renderVideoUrls = $this->mediaLibrary->availableVideoUrls();
        $this->renderLinkPickerOptions = LinkPicker::buildSelectOptions($this->pages);

        try {
            $slug = SlugCodec::encode($page->slug);
            $parts = [];

            foreach ($page->sections as $section) {
                if (!is_array($section)) {
                    continue;
                }
                $parts[] = $this->renderOne($slug, $section);
            }

            if ($parts === []) {
                return '<p class="dev-empty" id="dev-sections-empty"><i class="fa-solid fa-layer-group" aria-hidden="true"></i>Aucun bloc. Ajoutez-en un ci-dessous pour construire la page.</p>';
            }

            return implode("\n", $parts);
        } finally {
            $this->renderImageUrls = null;
            $this->renderVideoUrls = null;
            $this->renderLinkPickerOptions = null;
        }
    }

    /**
     * @param array<string, mixed> $section
     */
    public function renderSectionBody(string $slug, array $section): string
    {
        $this->renderImageUrls = $this->mediaLibrary->availableImageUrls();
        $this->renderVideoUrls = $this->mediaLibrary->availableVideoUrls();
        $this->renderLinkPickerOptions = LinkPicker::buildSelectOptions($this->pages);

        try {
            return $this->renderSectionBodyInner($slug, $section);
        } finally {
            $this->renderImageUrls = null;
            $this->renderVideoUrls = null;
            $this->renderLinkPickerOptions = null;
        }
    }

    /**
     * @param array<string, mixed> $section
     */
    private function renderOne(string $slug, array $section): string
    {
        $id = (string) ($section['id'] ?? '');
        $type = (string) ($section['type'] ?? '');
        $rawVariant = (string) ($section['variant'] ?? '');
        $variant = $this->variantResolver->resolve($type, $rawVariant);
        $visible = ($section['visible'] ?? true) !== false;

        $typeDef = $this->registry->getTypeDefinition($type);
        $label = is_string($typeDef['label'] ?? null) ? $typeDef['label'] : $type;
        $icon = is_string($typeDef['icon'] ?? null) && $typeDef['icon'] !== '' ? $typeDef['icon'] : self::FALLBACK_ICON;

        $cardClass = 'dev-section-card' . ($visible ? '' : ' dev-section-card--hidden');
        $actionUrl = '/dev/pages/' . $slug . '/sections/' . rawurlencode($id);
        $safeIdAttr = htmlspecialchars($id, ENT_QUOTES);
        $safeId = preg_replace('/[^a-zA-Z0-9_-]/', '', $id) ?? $id;

        $variantLabel = $variant !== '' ? $this->variantLabel($type, $variant) : '';

        $sectionJson = json_encode($section, JSON_UNESCAPED_UNICODE);
        $sectionJson = is_string($sectionJson) ? $sectionJson : '{}';

        $html = '<article class="' . $cardClass . '" id="section-' . $safeIdAttr . '" data-id="' . $safeIdAttr . '" data-section="' . htmlspecialchars($sectionJson, ENT_QUOTES) . '" data-dev-sortable-item draggable="true">';
        $html .= '<div class="dev-section-card__head">';
        $html .= '<button type="button" class="dev-icon-btn dev-icon-btn--drag" aria-label="Réorganiser le bloc"><i class="fa-solid fa-grip-vertical" aria-hidden="true"></i></button>';
        $html .= '<button type="button" class="dev-section-card__toggle" data-dev-accordion-toggle aria-expanded="false" aria-controls="section-body-' . $safeIdAttr . '">';
        $html .= '<i class="fa-solid fa-chevron-right dev-section-card__chevron" aria-hidden="true"></i>';
        $html .= '<span class="dev-section-card__icon" aria-hidden="true"><i class="' . $icon . '"></i></span>';
        $html .= '<span class="dev-section-card__title"><span>' . htmlspecialchars($label, ENT_QUOTES) . '</span>';
        if ($variantLabel !== '') {
            $html .= '<span class="dev-section-card__variant">' . htmlspecialchars($variantLabel, ENT_QUOTES) . '</span>';
        }
        $html .= '</span>';
        $html .= '</button>';
        $html .= '<div class="dev-section-card__actions">';
        $html .= '<label class="dev-switch dev-switch--sm" title="Afficher sur le site">';
        $html .= '<input form="form-' . $safeIdAttr . '" type="hidden" name="visible" value="0" />';
        $html .= '<input form="form-' . $safeIdAttr . '" type="checkbox" name="visible" value="1"' . ($visible ? ' checked' : '') . ' />';
        $html .= '<span class="dev-switch__track" aria-hidden="true"></span>';
        $html .= '<span class="visually-hidden">Afficher la section</span>';
        $html .= '</label>';
        $html .= $this->buttonForm($slug, $id, 'up', 'fa-arrow-up');
        $html .= $this->buttonForm($slug, $id, 'down', 'fa-arrow-down');
        $html .= '<form class="dev-inline-form" method="post" action="' . $actionUrl . '/delete" data-dev-section-delete>';
        $html .= '<button type="submit" class="dev-icon-btn dev-icon-btn--danger" title="Supprimer" aria-label="Supprimer le bloc"><i class="fa-solid fa-trash" aria-hidden="true"></i></button></form>';
        $html .= '</div></div>';

        $html .= $this->renderSectionBodyInner($slug, $section);
        $html .= '</article>';

        return $html;
    }

    /**
     * @param array<string, mixed> $section
     */
    private function renderSectionBodyInner(string $slug, array $section): string
    {
        $id = (string) ($section['id'] ?? '');
        $type = (string) ($section['type'] ?? '');
        $variant = $this->variantResolver->resolve($type, (string) ($section['variant'] ?? ''));
        $content = is_array($section['content'] ?? null) ? $section['content'] : [];
        $content = $this->normalizeContentForForm($content);
        $style = is_array($section['style'] ?? null) ? $section['style'] : [];

        $safeIdAttr = htmlspecialchars($id, ENT_QUOTES);
        $safeId = preg_replace('/[^a-zA-Z0-9_-]/', '', $id) ?? $id;
        $actionUrl = '/dev/pages/' . $slug . '/sections/' . rawurlencode($id);

        $html = '<div class="dev-section-card__body" id="section-body-' . $safeIdAttr . '">';
        $html .= '<form id="form-' . $safeIdAttr . '" class="dev-section-form" hx-post="' . $actionUrl . '" hx-trigger="change from:input:not([name=variant]), change from:select:not([name=variant]), change from:textarea, input delay:350ms from:input:not([name=variant])" hx-target="#section-saved-' . $safeIdAttr . '" hx-swap="innerHTML" data-dev-toast-form="Bloc enregistré">';

        $variants = $this->registry->getVariants($type);
        $html .= $this->renderFormToolbar($variant, $variants, $safeId);

        $contentHtml = $this->renderContentFields(
            $this->fieldSchema->contentFieldsForVariant($type, $variant),
            $content,
            $variant,
            $safeId,
            $slug,
            $id,
        );
        $styleHtml = $this->renderStyleAccordions(
            $type,
            $this->fieldSchema->styleFields($type),
            $style,
            $variant,
            $safeId,
        );

        if ($styleHtml !== '') {
            $tabContentId = 'section-tab-content-' . $safeId;
            $tabStyleId = 'section-tab-style-' . $safeId;
            $html .= '<div class="dev-section-form__tabs dev-section-form__tabs--editor" data-dev-tabs>';
            $html .= '<div class="dev-tabs__list dev-section-form__tablist" role="tablist" aria-label="Sections du formulaire de bloc">';
            $html .= '<button type="button" class="dev-tabs__tab is-active" role="tab" id="' . $tabContentId . '-btn" aria-selected="true" aria-controls="' . $tabContentId . '" data-tab="content" tabindex="0">'
                . '<i class="fa-solid fa-file-lines" aria-hidden="true"></i><span>Contenu</span></button>';
            $html .= '<button type="button" class="dev-tabs__tab" role="tab" id="' . $tabStyleId . '-btn" aria-selected="false" aria-controls="' . $tabStyleId . '" data-tab="appearance" tabindex="-1">'
                . '<i class="fa-solid fa-palette" aria-hidden="true"></i><span>Apparence</span></button>';
            $html .= '</div>';
            $html .= '<div class="dev-tabs__panel dev-section-form__panel is-active" role="tabpanel" id="' . $tabContentId . '" aria-labelledby="' . $tabContentId . '-btn" data-tab-panel="content">' . $contentHtml . '</div>';
            $html .= '<div class="dev-tabs__panel dev-section-form__panel" role="tabpanel" id="' . $tabStyleId . '" aria-labelledby="' . $tabStyleId . '-btn" data-tab-panel="appearance">' . $styleHtml . '</div>';
            $html .= '</div>';
        } else {
            $html .= '<div class="dev-section-form__panel dev-section-form__panel--solo">' . $contentHtml . '</div>';
        }

        $html .= '<footer class="dev-section-form__footer">';
        $html .= '<p class="dev-section-form__autosave-hint"><i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i><span>Enregistrement automatique</span></p>';
        $html .= '<div class="dev-section-form__status" id="section-saved-' . $safeIdAttr . '" aria-live="polite"></div>';
        $html .= '</footer>';
        $html .= '</form></div>';

        return $html;
    }

    /**
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private function normalizeContentForForm(array $content): array
    {
        if (isset($content['flip_words']) && is_array($content['flip_words'])) {
            $content['flip_words'] = implode("\n", array_map('strval', $content['flip_words']));
        }

        return $content;
    }

    /**
     * @param array<string, array{label: string}|string> $variants
     */
    private function renderFormToolbar(string $variant, array $variants, string $safeId): string
    {
        $html = '<div class="dev-section-form__toolbar">';
        if ($variants !== []) {
            $html .= '<div class="dev-section-form__toolbar-copy">';
            $html .= '<p class="dev-section-form__toolbar-title">Mise en page</p>';
            $html .= '</div>';
            $html .= '<div class="dev-section-form__toolbar-variant">';
            $html .= $this->fieldSelect('variant', 'Variante du bloc', $variant, $variants, $safeId, true);
            $html .= '</div>';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * @param array<string, mixed> $section
     */
    public function renderMediaField(string $slug, array $section, string $field, string $error = ''): string
    {
        $id = (string) ($section['id'] ?? '');
        $content = is_array($section['content'] ?? null) ? $section['content'] : [];
        $url = trim((string) ($content[$field] ?? ''));
        $kind = $field === 'video_url' || $field === 'background_video_url' ? 'video' : 'image';
        $library = $kind === 'video'
            ? ($this->renderVideoUrls ?? $this->mediaLibrary->availableVideoUrls())
            : ($this->renderImageUrls ?? $this->mediaLibrary->availableImageUrls());
        $accept = $this->libraryUploader->acceptAttribute($kind === 'video' ? 'library_video' : 'library_image');

        return SectionMediaFieldView::render($slug, $id, $field, $url, $kind, $library, $accept, $content, $error);
    }

    /**
     * @param array<string, mixed> $fields
     * @param array<string, mixed> $content
     */
    private function renderContentFields(array $fields, array $content, string $variant, string $safeId, string $slug, string $sectionId): string
    {
        $html = '<div class="dev-editor-layout">';
        $simpleFields = [];
        $mediaFields = [];
        $backgroundFields = [];
        $repeaters = [];

        foreach ($fields as $key => $field) {
            if (!is_array($field) || !$this->fieldAppliesToVariant($field, $variant)) {
                continue;
            }
            if ((string) ($field['group'] ?? '') === 'background') {
                $backgroundFields[$key] = $field;
                continue;
            }
            if (($field['type'] ?? '') === 'repeater') {
                if ($key === 'buttons') {
                    $repeaters['__buttons__'] = array_merge($field, ['type' => 'buttons']);
                } else {
                    $repeaters[$key] = $field;
                }
                continue;
            }
            if (($field['type'] ?? '') === 'buttons') {
                $repeaters['__buttons__'] = $field;
                continue;
            }
            if (in_array($field['type'] ?? '', ['image', 'video', 'shader'], true)) {
                $mediaFields[$key] = $field;
                continue;
            }
            $simpleFields[$key] = $field;
        }

        if ($backgroundFields !== []) {
            $html .= $this->renderEditorBlock(
                'Arrière-plan',
                '',
                'fa-image',
                SectionBackgroundFieldView::render(
                    $slug,
                    $sectionId,
                    $content,
                    $this->renderImageUrls ?? $this->mediaLibrary->availableImageUrls(),
                    $this->renderVideoUrls ?? $this->mediaLibrary->availableVideoUrls(),
                    $this->libraryUploader->acceptAttribute('library_image'),
                    $this->libraryUploader->acceptAttribute('library_video'),
                ),
            );
        }

        if ($simpleFields !== []) {
            $grid = '<div class="dev-form-grid dev-form-grid--1">';
            foreach ($simpleFields as $key => $field) {
                $fLabel = (string) ($field['label'] ?? $key);
                $value = (string) ($content[$key] ?? '');
                $name = 'content_' . $key;
                $inputType = match ($field['type'] ?? 'text') {
                    'textarea' => 'textarea',
                    'url' => 'url',
                    default => 'text',
                };
                $grid .= $this->fieldInput($name, $fLabel, $value, $inputType, $safeId);
            }
            $grid .= '</div>';
            $html .= $this->renderEditorBlock(
                'Textes et contenus',
                '',
                'fa-align-left',
                $grid,
            );
        }

        foreach ($mediaFields as $key => $field) {
            $fLabel = (string) ($field['label'] ?? 'Média');
            $html .= $this->renderEditorBlock(
                $fLabel,
                '',
                ($field['type'] ?? 'image') === 'video' ? 'fa-video' : 'fa-image',
                $this->renderMediaFieldBlock($slug, $sectionId, $key, $field, $content, false),
            );
        }

        foreach ($repeaters as $key => $field) {
            if ($key === '__buttons__') {
                $html .= $this->renderButtonsRepeater($field, $content, $safeId);
            } else {
                $html .= $this->renderRepeater($key, $field, $content, $safeId, $slug, $sectionId, $variant);
            }
        }

        $html .= '</div>';

        return $html;
    }

    private function renderEditorBlock(string $title, string $description, string $icon, string $bodyHtml): string
    {
        $descHtml = $description !== ''
            ? '<p class="dev-editor-block__desc">' . htmlspecialchars($description, ENT_QUOTES) . '</p>'
            : '';

        return '<section class="dev-editor-block">'
            . '<header class="dev-editor-block__head">'
            . '<span class="dev-editor-block__icon" aria-hidden="true"><i class="fa-solid ' . htmlspecialchars($icon, ENT_QUOTES) . '"></i></span>'
            . '<div class="dev-editor-block__copy">'
            . '<h3 class="dev-editor-block__title">' . htmlspecialchars($title, ENT_QUOTES) . '</h3>'
            . $descHtml
            . '</div></header>'
            . '<div class="dev-editor-block__body">' . $bodyHtml . '</div>'
            . '</section>';
    }

    /**
     * @param array<string, mixed> $fields
     * @param array<string, mixed> $style
     */
    private function renderStyleAccordions(string $type, array $fields, array $style, string $variant, string $safeId): string
    {
        $groups = [
            'general' => ['label' => 'Général', 'icon' => 'fa-sliders'],
            'layout' => ['label' => 'Mise en page', 'icon' => 'fa-up-right-and-down-left-from-center'],
            'typography' => ['label' => 'Typographie', 'icon' => 'fa-font'],
            'visual' => ['label' => 'Visuel', 'icon' => 'fa-image'],
        ];

        $byGroup = [];
        foreach ($fields as $key => $field) {
            if (!is_array($field) || !$this->fieldAppliesToVariant($field, $variant)) {
                continue;
            }
            $group = (string) ($field['group'] ?? 'general');
            $byGroup[$group][$key] = $field;
        }

        if ($byGroup === []) {
            return '';
        }

        $html = '<div class="dev-section-form__accordions">';
        $openFirst = true;
        foreach ($groups as $groupKey => $groupMeta) {
            $groupFields = $byGroup[$groupKey] ?? [];
            if ($groupFields === []) {
                continue;
            }
            $openAttr = $openFirst ? ' open' : '';
            $openFirst = false;
            $html .= '<details class="dev-form-group"' . $openAttr . '>';
            $html .= '<summary class="dev-form-group__summary">';
            $html .= '<i class="fa-solid ' . $groupMeta['icon'] . '" aria-hidden="true"></i>';
            $html .= '<span>' . htmlspecialchars($groupMeta['label'], ENT_QUOTES) . '</span>';
            $html .= '<i class="fa-solid fa-chevron-down dev-form-group__chevron" aria-hidden="true"></i>';
            $html .= '</summary>';
            $html .= '<div class="dev-form-group__body dev-form-grid dev-form-grid--2">';
            foreach ($groupFields as $key => $field) {
                $fLabel = (string) ($field['label'] ?? $key);
                $name = 'style_' . $key;
                $fallback = $type === 'hero' ? (HeroStyle::defaults($variant)[$key] ?? '') : (SectionDefaults::style($type)[$key] ?? '');
                $current = (string) ($style[$key] ?? $fallback);
                if (($field['type'] ?? '') === 'select' && is_string($field['options'] ?? null)) {
                    $options = array_map('trim', explode(',', trim($field['options'], '[]')));
                    $html .= $this->fieldSelectRaw($name, $fLabel, $current, $this->styleSelectOptions($key, $options), $safeId);
                } elseif (($field['type'] ?? '') === 'color-token') {
                    $html .= $this->fieldSelectRaw($name, $fLabel, $current, [
                        'primary' => 'Primaire',
                        'muted' => 'Atténué',
                        'background' => 'Fond de page',
                    ], $safeId, false, true);
                } elseif (($field['type'] ?? '') === 'color') {
                    $html .= $this->fieldStyleColor($name, $fLabel, $current, $safeId);
                }
            }
            $html .= '</div></details>';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * @param array<string, mixed> $field
     */
    private function fieldAppliesToVariant(array $field, string $variant): bool
    {
        return $this->fieldSchema->fieldAppliesToVariant($field, $variant);
    }

    private function variantLabel(string $type, string $variant): string
    {
        $variants = $this->registry->getVariants($type);
        $def = $variants[$variant] ?? null;
        if (is_string($def)) {
            return $def;
        }
        if (is_array($def)) {
            return (string) ($def['label'] ?? $variant);
        }

        return $variant;
    }

    public function resolvedVariantLabel(string $type, string $variant): string
    {
        return $this->variantLabel($type, $this->variantResolver->resolve($type, $variant));
    }

    /**
     * @param array<string, mixed> $field
     * @param array<string, mixed> $content
     */
    private function renderMediaFieldBlock(string $slug, string $sectionId, string $key, array $field, array $content, bool $compact): string
    {
        $url = trim((string) ($content[$key] ?? ''));
        $kind = ($field['type'] ?? 'image') === 'video' ? 'video' : 'image';
        $library = $kind === 'video'
            ? ($this->renderVideoUrls ?? $this->mediaLibrary->availableVideoUrls())
            : ($this->renderImageUrls ?? $this->mediaLibrary->availableImageUrls());
        $accept = $this->libraryUploader->acceptAttribute($kind === 'video' ? 'library_video' : 'library_image');

        if ($compact) {
            return SectionMediaFieldView::renderCompact($slug, $sectionId, $key, $url, $kind, $library, $accept, $content);
        }

        return SectionMediaFieldView::render($slug, $sectionId, $key, $url, $kind, $library, $accept, $content);
    }

    /**
     * @param array<string, mixed> $field
     * @param array<string, mixed> $content
     */
    private function renderRepeater(string $key, array $field, array $content, string $sectionId, string $slug, string $rawSectionId, string $variant = 'default'): string
    {
        $items = is_array($content[$key] ?? null) ? $content[$key] : [];
        $fLabel = (string) ($field['label'] ?? 'Éléments');
        $safeKey = htmlspecialchars($key, ENT_QUOTES);

        $itemFields = is_array($field['fields'] ?? null) && $field['fields'] !== []
            ? $field['fields']
            : ['title' => ['type' => 'text', 'label' => 'Titre'], 'text' => ['type' => 'textarea', 'label' => 'Texte']];

        $rowCount = max(count($items), 1);

        $html = '<details class="dev-form-group dev-form-group--repeater" open>';
        $html .= '<summary class="dev-form-group__summary">';
        $html .= '<i class="fa-solid fa-list" aria-hidden="true"></i>';
        $html .= '<span>' . htmlspecialchars($fLabel, ENT_QUOTES) . '</span>';
        $html .= '<i class="fa-solid fa-chevron-down dev-form-group__chevron" aria-hidden="true"></i>';
        $html .= '</summary>';
        $html .= '<div class="dev-form-group__body">';
        $html .= '<p class="dev-hint">Les éléments entièrement vides sont retirés à l\'enregistrement. Images et vidéos : bibliothèque dans <a href="/dev/medias">Médias</a>.</p>';
        $html .= '<div class="dev-repeater dev-repeater--items" data-items-repeater data-repeater-key="' . $safeKey . '">';
        $html .= '<div class="dev-repeater__list" data-items-repeater-list>';

        for ($i = 0; $i < $rowCount; $i++) {
            $item = is_array($items[$i] ?? null) ? $items[$i] : [];
            $html .= $this->renderRepeaterItemRow($key, $i, $item, $itemFields, $variant, $sectionId, $slug, $rawSectionId);
        }

        $html .= '</div>';
        $html .= '<button type="button" class="dev-button dev-button--ghost dev-button--sm" data-items-repeater-add>'
            . '<i class="fa-solid fa-plus" aria-hidden="true"></i> Ajouter un élément</button>';
        $html .= '<template data-items-repeater-template>'
            . $this->renderRepeaterItemRow($key, '__INDEX__', [], $itemFields, $variant, $sectionId, $slug, $rawSectionId)
            . '</template>';
        $html .= '</div></div></details>';

        return $html;
    }

    /**
     * @param array<string, mixed> $item
     * @param array<string, mixed> $itemFields
     */
    private function renderRepeaterItemRow(
        string $repeaterKey,
        int|string $index,
        array $item,
        array $itemFields,
        string $variant,
        string $sectionId,
        string $slug,
        string $rawSectionId,
    ): string {
        $indexKey = is_int($index) ? (string) $index : (string) $index;
        $displayIndex = is_int($index) ? (string) ($index + 1) : '';
        $idSuffix = is_int($index) ? $sectionId . '-item' . $index : $sectionId . '-item__INDEX__';

        $html = '<div class="dev-repeater__item" data-items-repeater-row>';
        $html .= '<span class="dev-repeater__index" aria-hidden="true">' . htmlspecialchars($displayIndex, ENT_QUOTES) . '</span>';
        $html .= '<div class="dev-repeater__item-fields">';

        foreach ($itemFields as $fKey => $fDef) {
            if (!is_array($fDef) || !$this->fieldAppliesToVariant($fDef, $variant)) {
                continue;
            }
            $subLabel = (string) ($fDef['label'] ?? $fKey);
            $value = (string) ($item[$fKey] ?? '');
            $name = 'content_' . $repeaterKey . '_' . $indexKey . '_' . $fKey;
            if (in_array($fDef['type'] ?? '', ['image', 'video'], true)) {
                $html .= $this->renderRepeaterMediaField($slug, $rawSectionId, $name, $fKey, $fDef, $item, $idSuffix, $repeaterKey);
                continue;
            }
            if (($fDef['type'] ?? '') === 'icon') {
                $defaultIndex = is_int($index) ? $index + 1 : 1;
                $html .= $this->renderIconField($name, $subLabel, $value, $idSuffix, $defaultIndex);
                continue;
            }
            if (($fDef['type'] ?? '') === 'select') {
                $html .= $this->fieldSelectRaw($name, $subLabel, $value, $this->repeaterFieldSelectOptions($fDef), $idSuffix);
                continue;
            }
            $subType = ($fDef['type'] ?? 'text') === 'textarea' ? 'textarea' : 'text';
            $html .= $this->fieldInput($name, $subLabel, $value, $subType, $idSuffix);
        }

        $html .= '</div>';
        $html .= '<button type="button" class="dev-icon-btn dev-icon-btn--danger" data-items-repeater-remove aria-label="Supprimer cet élément" title="Supprimer">'
            . '<i class="fa-solid fa-trash" aria-hidden="true"></i></button>';
        $html .= '</div>';

        return $html;
    }

    private function renderIconField(string $name, string $label, string $value, string $idPrefix, int $defaultIndex = 1): string
    {
        $glyph = FontAwesomeIcon::glyph($value, FontAwesomeIcon::defaultForIndex($defaultIndex));
        $fieldId = $idPrefix . '-' . $name;
        $safeId = htmlspecialchars($fieldId, ENT_QUOTES);
        $safeName = htmlspecialchars($name, ENT_QUOTES);
        $safeLabel = htmlspecialchars($label, ENT_QUOTES);
        $labelText = FontAwesomeIcon::catalog()[$glyph] ?? $glyph;

        $html = '<div class="dev-field dev-field--icon">';
        $html .= '<span class="dev-label">' . $safeLabel . '</span>';
        $html .= '<input type="hidden" class="dev-input dev-input--icon" id="' . $safeId . '" name="' . $safeName . '" value="' . htmlspecialchars($glyph, ENT_QUOTES) . '" data-dev-icon-input />';
        $html .= '<div class="dev-icon-picker" data-dev-icon-picker>';
        $html .= '<div class="dev-icon-picker__preview" aria-live="polite">';
        $html .= '<span class="dev-icon-picker__glyph" aria-hidden="true"><i class="' . htmlspecialchars(FontAwesomeIcon::solidClass($glyph), ENT_QUOTES) . '"></i></span>';
        $html .= '<span class="dev-icon-picker__label" data-dev-icon-label>' . htmlspecialchars($labelText, ENT_QUOTES) . '</span>';
        $html .= '</div>';
        $html .= '<div class="dev-icon-picker__grid" role="listbox" aria-label="Choisir une icône">';
        foreach (FontAwesomeIcon::catalog() as $iconGlyph => $iconLabel) {
            $selected = $iconGlyph === $glyph ? ' dev-icon-picker__btn--selected' : '';
            $html .= '<button type="button" class="dev-icon-picker__btn' . $selected . '" role="option" aria-label="' . htmlspecialchars($iconLabel, ENT_QUOTES) . '"'
                . ' data-dev-icon-pick data-target="' . $safeId . '" data-icon="' . htmlspecialchars($iconGlyph, ENT_QUOTES) . '"'
                . ' data-label="' . htmlspecialchars($iconLabel, ENT_QUOTES) . '"'
                . ($iconGlyph === $glyph ? ' aria-selected="true"' : ' aria-selected="false"') . '>';
            $html .= '<i class="' . htmlspecialchars(FontAwesomeIcon::solidClass($iconGlyph), ENT_QUOTES) . '" aria-hidden="true"></i>';
            $html .= '</button>';
        }
        $html .= '</div></div></div>';

        return $html;
    }

    /**
     * @param array<string, mixed> $fDef
     * @param array<string, mixed> $item
     */
    private function renderRepeaterMediaField(
        string $slug,
        string $sectionId,
        string $inputName,
        string $fieldKey,
        array $fDef,
        array $item,
        string $idPrefix,
        string $repeaterKey = 'items',
    ): string {
        $value = trim((string) ($item[$fieldKey] ?? ''));
        $kind = ($fDef['type'] ?? 'image') === 'video' ? 'video' : 'image';
        $library = $kind === 'video'
            ? ($this->renderVideoUrls ?? $this->mediaLibrary->availableVideoUrls())
            : ($this->renderImageUrls ?? $this->mediaLibrary->availableImageUrls());
        $fieldId = $idPrefix . '-' . $inputName;
        $safeInputId = htmlspecialchars($fieldId, ENT_QUOTES);
        $label = (string) ($fDef['label'] ?? ($kind === 'video' ? 'Vidéo' : 'Image'));

        $libraryHtml = '';
        $limit = 6;
        foreach (array_slice($library, 0, $limit) as $url) {
            $safeUrl = htmlspecialchars($url, ENT_QUOTES);
            $selected = $url === $value ? ' dev-media-library__pick--selected' : '';
            $thumb = $kind === 'video'
                ? '<i class="fa-solid fa-file-video" aria-hidden="true"></i>'
                : '<img src="' . $safeUrl . '" alt="" loading="lazy" decoding="async" />';
            $libraryHtml .= '<button type="button" class="dev-media-library__pick dev-media-library__pick--sm' . $selected . '" data-dev-repeater-media-pick data-target="' . $safeInputId . '" data-url="' . $safeUrl . '" aria-label="Utiliser ce média">' . $thumb . '</button>';
        }

        $libraryBlock = '';
        if ($libraryHtml !== '') {
            $urlsAttr = htmlspecialchars(json_encode(array_values($library), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ENT_QUOTES);
            $more = count($library) > $limit
                ? '<button type="button" class="dev-media-library__more dev-button dev-button--ghost dev-button--sm" data-dev-media-library-open>Voir toute la bibliothèque (' . count($library) . ')</button>'
                : '';
            $libraryBlock = '<div class="dev-media-library" data-media-library-urls="' . $urlsAttr . '" data-media-library-kind="' . htmlspecialchars($kind, ENT_QUOTES) . '" data-media-library-current="' . htmlspecialchars($value, ENT_QUOTES) . '">'
                . '<div class="dev-media-library__grid dev-media-library__grid--inline">' . $libraryHtml . '</div>'
                . $more
                . '</div>';
        }

        return '<div class="dev-field dev-field--repeater-media">'
            . '<label class="dev-label" for="' . $safeInputId . '">' . htmlspecialchars($label, ENT_QUOTES) . '</label>'
            . '<input class="dev-input" type="text" id="' . $safeInputId . '" name="' . htmlspecialchars($inputName, ENT_QUOTES) . '" value="' . htmlspecialchars($value, ENT_QUOTES) . '" />'
            . $libraryBlock
            . ($kind === 'image' ? $this->renderRepeaterImageFitSelect($item, $inputName, $idPrefix, $repeaterKey, $value !== '') : '')
            . '</div>';
    }

    /**
     * @param array<string, mixed> $item
     */
    private function renderRepeaterImageFitSelect(array $item, string $inputName, string $idPrefix, string $repeaterKey, bool $visible): string
    {
        $index = 0;
        $pattern = '/content_' . preg_quote($repeaterKey, '/') . '_(\d+)_/';
        if (preg_match($pattern, $inputName, $m) === 1) {
            $index = (int) $m[1];
        }
        $current = MediaDisplaySettings::imageFit($item);
        $name = 'content_' . $repeaterKey . '_' . $index . '_image_fit';
        $id = htmlspecialchars($idPrefix . '-image-fit', ENT_QUOTES);
        $hidden = $visible ? '' : ' hidden';
        $options = [
            'cover' => 'Remplir (cover)',
            'contain' => 'Contenir (contain)',
            'fill' => 'Étirer (fill)',
            'none' => 'Taille réelle (none)',
            'scale-down' => 'Réduire si besoin',
        ];
        $html = '<div class="dev-field dev-field--repeater-media-fit"' . $hidden . ' data-dev-repeater-media-fit><label class="dev-label" for="' . $id . '">Adaptation</label><select class="dev-input dev-select" id="' . $id . '" name="' . htmlspecialchars($name, ENT_QUOTES) . '">';
        foreach ($options as $fit => $fitLabel) {
            $html .= '<option value="' . htmlspecialchars($fit, ENT_QUOTES) . '"' . ($current === $fit ? ' selected' : '') . '>' . htmlspecialchars($fitLabel, ENT_QUOTES) . '</option>';
        }
        $html .= '</select></div>';

        return $html;
    }

    /**
     * @param array<string, array<string, mixed>|string> $variants
     */
    private function fieldSelect(string $name, string $label, string $current, array $variants, string $idPrefix = '', bool $variantRefresh = false): string
    {
        $options = [];
        foreach ($variants as $key => $def) {
            if (is_string($def)) {
                $vLabel = $def;
            } elseif (is_array($def)) {
                $vLabel = (string) ($def['label'] ?? $key);
            } else {
                $vLabel = (string) $key;
            }
            $options[(string) $key] = $vLabel;
        }

        return $this->fieldSelectRaw($name, $label, $current, $options, $idPrefix, $variantRefresh);
    }

    /**
     * @param array<string, mixed> $field
     * @param array<string, mixed> $content
     */
    private function renderButtonsRepeater(array $field, array $content, string $sectionId): string
    {
        $fLabel = (string) ($field['label'] ?? 'Boutons');

        $buttons = is_array($content['buttons'] ?? null) ? $content['buttons'] : null;
        if ($buttons === null) {
            $legacyLabel = $content['cta_label'] ?? $content['button_label'] ?? null;
            $legacyHref = $content['cta_href'] ?? $content['button_href'] ?? null;
            $buttons = ($legacyLabel !== null || $legacyHref !== null)
                ? [['label' => $legacyLabel ?? '', 'href' => $legacyHref ?? '', 'style' => 'primary']]
                : [];
        }
        $buttons = array_values($buttons);

        $rows = [];
        foreach ($buttons as $i => $button) {
            $rows[] = $this->buttonRow((string) $i, is_array($button) ? $button : [], $sectionId);
        }

        $safeSectionId = htmlspecialchars($sectionId, ENT_QUOTES);
        $html = '<details class="dev-form-group dev-form-group--buttons" open>';
        $html .= '<summary class="dev-form-group__summary">';
        $html .= '<i class="fa-solid fa-hand-pointer" aria-hidden="true"></i>';
        $html .= '<span>' . htmlspecialchars($fLabel, ENT_QUOTES) . '</span>';
        $html .= '<i class="fa-solid fa-chevron-down dev-form-group__chevron" aria-hidden="true"></i>';
        $html .= '</summary>';
        $html .= '<div class="dev-form-group__body">';
        $html .= '<input type="hidden" name="content_buttons_count" value="' . count($buttons) . '" data-buttons-repeater-count />';
        $html .= '<div class="dev-repeater dev-repeater--buttons" data-buttons-repeater-list>' . implode('', $rows) . '</div>';
        $html .= '<button type="button" class="dev-button dev-button--ghost dev-button--sm" data-buttons-repeater-add data-buttons-repeater-section="' . $safeSectionId . '">'
            . '<i class="fa-solid fa-plus" aria-hidden="true"></i> Ajouter un bouton</button>';
        $html .= '<template data-buttons-repeater-template>' . $this->buttonRow('__INDEX__', [], $sectionId) . '</template>';
        $html .= '</div></details>';

        return $html;
    }

    /**
     * @param array<string, mixed> $button
     */
    private function buttonRow(string $index, array $button, string $sectionId): string
    {
        $label = (string) ($button['label'] ?? '');
        $href = (string) ($button['href'] ?? '');
        $style = \Capsule\Section\Support\SectionButtonStyle::normalize((string) ($button['style'] ?? 'primary'));
        $idPrefix = $sectionId . '-btn' . $index;
        $labelName = 'content_buttons_' . $index . '_label';
        $hrefName = 'content_buttons_' . $index . '_href';
        $styleName = 'content_buttons_' . $index . '_style';
        $hrefFieldId = $idPrefix . '-' . $hrefName;

        $html = '<div class="dev-repeater__item dev-repeater__item--button" data-buttons-repeater-row>';
        $html .= '<div class="dev-repeater__item-fields">';
        $html .= '<div class="dev-repeater__item-field--label">' . $this->fieldInput($labelName, 'Libellé', $label, 'text', $idPrefix) . '</div>';
        $html .= '<div class="dev-field dev-repeater__item-field--style"><label class="dev-label" for="' . htmlspecialchars($idPrefix . '-' . $styleName, ENT_QUOTES) . '">Style</label>'
            . '<select class="dev-input dev-select" id="' . htmlspecialchars($idPrefix . '-' . $styleName, ENT_QUOTES) . '" name="' . htmlspecialchars($styleName, ENT_QUOTES) . '">'
            . '<option value="primary"' . ($style === 'primary' ? ' selected' : '') . '>Principal</option>'
            . '<option value="secondary"' . ($style === 'secondary' ? ' selected' : '') . '>Secondaire</option>'
            . '<option value="outline"' . ($style === 'outline' ? ' selected' : '') . '>Contour</option>'
            . '</select></div>';
        $html .= '<div class="dev-field dev-repeater__item-field--href"><label class="dev-label" for="' . htmlspecialchars($hrefFieldId, ENT_QUOTES) . '">Lien</label>'
            . LinkPicker::render($hrefFieldId, $hrefName, $href, $this->pages, '', false, $this->renderLinkPickerOptions) . '</div>';
        $html .= '</div>';
        $html .= '<button type="button" class="dev-icon-btn dev-icon-btn--danger" data-buttons-repeater-remove aria-label="Supprimer ce bouton" title="Supprimer">'
            . '<i class="fa-solid fa-trash" aria-hidden="true"></i></button>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Libellés lisibles pour les champs de style à options fixes.
     *
     * @param list<string> $rawOptions
     *
     * @return array<string, string>
     */
    private function styleSelectOptions(string $fieldKey, array $rawOptions): array
    {
        $labelMaps = [
            'padding' => [
                'sm' => 'Compact',
                'md' => 'Normal',
                'lg' => 'Large',
                'xl' => 'Très large',
            ],
            'text_align' => [
                'left' => 'Gauche',
                'center' => 'Centre',
                'right' => 'Droite',
            ],
            'min_height' => [
                'auto' => 'Automatique',
                'large' => 'Grande',
                'viewport' => 'Plein écran',
            ],
            'content_width' => [
                'narrow' => 'Étroit',
                'default' => 'Standard',
                'wide' => 'Large',
            ],
            'title_size' => [
                'inherit' => 'Thème (automatique)',
                'sm' => 'Petit',
                'md' => 'Moyen',
                'lg' => 'Grand',
                'xl' => 'Très grand',
                'display' => 'Affichage',
            ],
            'subtitle_size' => [
                'inherit' => 'Thème (automatique)',
                'sm' => 'Petit',
                'md' => 'Moyen',
                'lg' => 'Grand',
                'hidden' => 'Masqué',
            ],
            'image_border' => [
                'none' => 'Aucune',
                'thin' => 'Fine',
            ],
            'border' => [
                'none' => 'Aucune',
                'yes' => 'Afficher',
            ],
            'image_radius' => [
                'none' => 'Aucun',
                'md' => 'Moyen',
                'lg' => 'Grand',
            ],
            'image_shadow' => [
                'none' => 'Aucune',
                'md' => 'Légère',
            ],
        ];
        $labels = $labelMaps[$fieldKey] ?? [];

        $options = [];
        foreach ($rawOptions as $value) {
            $options[$value] = $labels[$value] ?? $value;
        }

        return $options;
    }

    /**
     * @param array<string, mixed> $field
     *
     * @return array<string, string>
     */
    private function repeaterFieldSelectOptions(array $field): array
    {
        $raw = $field['options'] ?? '';
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $options = array_map('trim', explode(',', trim($raw, " \t\n\r\0\x0B[]")));
        $result = [];
        foreach ($options as $option) {
            if ($option === '') {
                continue;
            }
            $result[$option] = match ($option) {
                'primary' => 'Principal',
                'secondary' => 'Secondaire',
                'outline' => 'Contour',
                default => $option,
            };
        }

        return $result;
    }

    /**
     * @param array<string|int, string> $options
     */
    private function fieldSelectRaw(string $name, string $label, string $current, array $options, string $idPrefix = '', bool $variantRefresh = false, bool $fullWidth = false): string
    {
        $fieldId = ($idPrefix !== '' ? $idPrefix . '-' : '') . $name;
        $fieldClass = 'dev-field' . ($fullWidth ? ' dev-field--full' : '');
        $html = '<div class="' . $fieldClass . '"><label class="dev-label" for="' . htmlspecialchars($fieldId, ENT_QUOTES) . '">'
            . htmlspecialchars($label, ENT_QUOTES) . '</label>';
        $refreshAttr = $variantRefresh ? ' data-dev-variant-refresh="1"' : '';
        $html .= '<select class="dev-input dev-select" id="' . htmlspecialchars($fieldId, ENT_QUOTES) . '" name="' . htmlspecialchars($name, ENT_QUOTES) . '"' . $refreshAttr . '>';
        foreach ($options as $value => $optLabel) {
            $optValue = is_int($value) ? (string) $optLabel : (string) $value;
            $text = (string) $optLabel;
            $selected = $optValue === $current ? ' selected' : '';
            $html .= '<option value="' . htmlspecialchars($optValue, ENT_QUOTES) . '"' . $selected . '>'
                . htmlspecialchars($text, ENT_QUOTES) . '</option>';
        }
        $html .= '</select></div>';

        return $html;
    }

    private function fieldStyleColor(string $name, string $label, string $value, string $idPrefix = ''): string
    {
        $fieldId = ($idPrefix !== '' ? $idPrefix . '-' : '') . $name;
        $raw = trim($value);
        $normalized = $raw !== '' ? ThemeColor::normalize($raw, '#0f172a') : '';
        $pickerValue = $normalized !== '' ? $normalized : '#0f172a';
        $safeName = htmlspecialchars($name, ENT_QUOTES);
        $safeLabel = htmlspecialchars($label, ENT_QUOTES);
        $safeId = htmlspecialchars($fieldId, ENT_QUOTES);
        $safeValue = htmlspecialchars($normalized, ENT_QUOTES);
        $safePicker = htmlspecialchars($pickerValue, ENT_QUOTES);

        return '<div class="dev-field dev-field--color dev-field--full">'
            . '<label class="dev-label" for="' . $safeId . '">' . $safeLabel . '</label>'
            . '<div class="dev-color-field dev-color-field--block" data-color-sync>'
            . '<input type="color" id="' . $safeId . '" value="' . $safePicker . '" data-style-color-picker aria-label="Nuancier ' . $safeLabel . '" />'
            . '<input class="dev-input dev-input--sm" type="text" name="' . $safeName . '" data-color-text value="' . $safeValue . '" placeholder="Thème (automatique)" aria-label="Valeur hexadécimale ' . $safeLabel . '" />'
            . '</div></div>';
    }

    private function fieldInput(string $name, string $label, string $value, string $type, string $idPrefix = ''): string
    {
        $fieldId = ($idPrefix !== '' ? $idPrefix . '-' : '') . $name;
        $safeName = htmlspecialchars($name, ENT_QUOTES);
        $safeLabel = htmlspecialchars($label, ENT_QUOTES);
        $safeValue = htmlspecialchars($value, ENT_QUOTES);
        $safeId = htmlspecialchars($fieldId, ENT_QUOTES);

        $fieldClass = 'dev-field' . ($type === 'textarea' ? ' dev-field--full' : '');
        $html = '<div class="' . $fieldClass . '"><label class="dev-label" for="' . $safeId . '">' . $safeLabel . '</label>';
        if ($type === 'textarea') {
            $html .= '<textarea class="dev-input dev-textarea" id="' . $safeId . '" name="' . $safeName . '" rows="4">'
                . $safeValue . '</textarea>';
        } else {
            $html .= '<input class="dev-input" id="' . $safeId . '" type="' . htmlspecialchars($type, ENT_QUOTES) . '" name="' . $safeName . '" value="' . $safeValue . '" />';
        }
        $html .= '</div>';

        return $html;
    }

    private function buttonForm(string $slug, string $id, string $direction, string $icon): string
    {
        $title = $direction === 'up' ? 'Monter le bloc' : 'Descendre le bloc';

        return '<form class="dev-inline-form" method="post" action="/dev/pages/' . $slug . '/sections/' . rawurlencode($id) . '/move" data-dev-ajax="sections-move">'
            . '<input type="hidden" name="direction" value="' . $direction . '" />'
            . '<button type="submit" class="dev-icon-btn" title="' . $title . '" aria-label="' . $title . '"><i class="fa-solid ' . $icon . '" aria-hidden="true"></i></button></form>';
    }
}
