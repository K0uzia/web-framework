<?php

declare(strict_types=1);

namespace App\Http\Admin;

use App\Http\Dev\LinkPicker;
use App\Http\Dev\Sections\ClientAccessKinds;
use Capsule\ClientDashboardConfig;
use Capsule\MediaLibrary;
use Capsule\MediaRepository;
use Capsule\Page;
use Capsule\PageRepository;
use Capsule\Section\SectionFieldSchema;
use Capsule\Section\Support\SectionButtonStyle;
use Capsule\SectionRegistry;

/**
 * Formulaire d'édition client : blocs en accordéon, images en grille, liens via pages du site.
 */
final class PageEditFormRenderer
{
    private const GROUP_TEXT = 'text';
    private const GROUP_MEDIA = 'media';
    private const GROUP_LINK = 'link';
    private const GROUP_LIST = 'list';
    private const GROUP_OTHER = 'other';

    /** @var array<string, array{label: string, icon: string}> */
    private const GROUP_META = [
        self::GROUP_TEXT => ['label' => 'Textes', 'icon' => 'fa-align-left'],
        self::GROUP_MEDIA => ['label' => 'Images et médias', 'icon' => 'fa-image'],
        self::GROUP_LINK => ['label' => 'Liens et boutons', 'icon' => 'fa-link'],
        self::GROUP_LIST => ['label' => 'Listes', 'icon' => 'fa-list'],
        self::GROUP_OTHER => ['label' => 'Autres réglages', 'icon' => 'fa-sliders'],
    ];

    /** @var string|null */
    private ?string $linkOptionsCache = null;

    public function __construct(
        private readonly SectionRegistry $registry,
        private readonly SectionFieldSchema $fieldSchema,
        private readonly MediaLibrary $mediaLibrary,
        private readonly MediaRepository $media,
        private readonly PageRepository $pages,
    ) {
    }

    public static function fieldPrefix(string $sectionId): string
    {
        return 's_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $sectionId) . '__';
    }

    /**
     * @param array{medias_enabled?: bool, pages: array<string, array{sections: array<string, array{fields: list<string>}>}>} $config
     */
    public function render(Page $page, array $config): string
    {
        if (ClientDashboardConfig::isMediasEnabled($config)) {
            $this->mediaLibrary->syncClientRecords('image');
            $this->mediaLibrary->syncClientRecords('video');
        }

        $parts = [];
        $index = 0;
        foreach ($page->sections as $section) {
            if (!is_array($section)) {
                continue;
            }
            $html = $this->renderSection($page->slug, $section, $config, $index + 1, $index === 0);
            if ($html !== '') {
                $parts[] = $html;
                $index++;
            }
        }

        if ($parts === []) {
            return '<div class="admin-empty admin-empty--compact">'
                . '<p class="admin-empty__title">Rien à modifier ici</p>'
                . '<p class="admin-empty__text">Aucun contenu n\'a été ouvert à l\'édition sur cette page.</p>'
                . '</div>';
        }

        return '<div class="admin-edit-sections" data-admin-accordion>' . implode('', $parts) . '</div>';
    }

    /**
     * @param array<string, mixed>                                                                                                                                 $section
     * @param array{medias_enabled?: bool, pages: array<string, array{sections: array<string, array{fields: list<string>}>}>} $config
     */
    private function renderSection(
        string $pageSlug,
        array $section,
        array $config,
        int $blockNumber,
        bool $open,
    ): string {
        $sectionId = is_string($section['id'] ?? null) ? $section['id'] : '';
        $type = is_string($section['type'] ?? null) ? $section['type'] : '';
        $variant = is_string($section['variant'] ?? null) ? $section['variant'] : '';
        if ($sectionId === '' || $type === '') {
            return '';
        }

        $allowedKeys = ClientDashboardConfig::allowedFields($config, $pageSlug, $sectionId);
        if ($allowedKeys === []) {
            return '';
        }

        $defs = $this->fieldSchema->contentFieldsForVariant($type, $variant);
        $allowedKeys = ClientAccessKinds::resolveAllowedFields($defs, $allowedKeys);
        $allowedSet = array_fill_keys($allowedKeys, true);
        $fields = [];
        foreach ($defs as $key => $def) {
            if (!is_string($key) || !isset($allowedSet[$key]) || !is_array($def)) {
                continue;
            }
            $fields[$key] = $def;
        }
        if ($fields === []) {
            return '';
        }

        $content = is_array($section['content'] ?? null) ? $section['content'] : [];
        $typeDef = $this->registry->getTypeDefinition($type);
        $typeLabel = is_string($typeDef['label'] ?? null) ? $typeDef['label'] : $type;
        $contentTitle = trim((string) ($content['title'] ?? ''));
        $displayTitle = $contentTitle !== '' ? $contentTitle : $typeLabel;
        $mediasEnabled = ClientDashboardConfig::isMediasEnabled($config);
        $prefix = self::fieldPrefix($sectionId);
        $grouped = $this->groupFields($fields);

        $groupsHtml = [];
        foreach (self::GROUP_META as $groupKey => $meta) {
            $groupFields = $grouped[$groupKey] ?? [];
            if ($groupFields === []) {
                continue;
            }
            $fieldsHtml = [];
            foreach ($groupFields as $key => $field) {
                $fieldsHtml[] = $this->renderField(
                    $sectionId,
                    $prefix,
                    $key,
                    $field,
                    $content,
                    $mediasEnabled,
                    $variant,
                );
            }
            $groupsHtml[] = $this->renderGroup($meta['label'], $meta['icon'], implode('', $fieldsHtml));
        }

        $safeId = htmlspecialchars($sectionId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $title = htmlspecialchars($displayTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $typeHint = htmlspecialchars($typeLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $fieldCount = count($fields);
        $openAttr = $open ? ' open' : '';

        return '<details class="admin-edit-section" data-admin-block' . $openAttr . '>'
            . '<summary class="admin-edit-section__summary">'
            . '<span class="admin-edit-section__summary-main">'
            . '<span class="admin-edit-section__eyebrow">Bloc ' . $blockNumber . ' · ' . $typeHint . '</span>'
            . '<span class="admin-edit-section__title" id="admin-sec-' . $safeId . '">' . $title . '</span>'
            . '<span class="admin-edit-section__meta">' . $fieldCount . ' champ' . ($fieldCount > 1 ? 's' : '') . '</span>'
            . '</span>'
            . '<i class="fa-solid fa-chevron-down admin-edit-section__chevron" aria-hidden="true"></i>'
            . '</summary>'
            . '<div class="admin-edit-section__body">'
            . implode('', $groupsHtml)
            . '</div>'
            . '</details>';
    }

    /**
     * @param array<string, array<string, mixed>> $fields
     *
     * @return array<string, array<string, array<string, mixed>>>
     */
    private function groupFields(array $fields): array
    {
        $grouped = [
            self::GROUP_TEXT => [],
            self::GROUP_MEDIA => [],
            self::GROUP_LINK => [],
            self::GROUP_LIST => [],
            self::GROUP_OTHER => [],
        ];

        foreach ($fields as $key => $field) {
            $grouped[$this->displayGroupFor($key, $field)][$key] = $field;
        }

        return $grouped;
    }

    /**
     * @param array<string, mixed> $field
     */
    private function displayGroupFor(string $key, array $field): string
    {
        $type = (string) ($field['type'] ?? 'text');
        if ($type === 'repeater') {
            return self::GROUP_LIST;
        }
        if ($type === 'image' || $type === 'video') {
            return self::GROUP_MEDIA;
        }

        $kind = ClientAccessKinds::kindFor($key, $field);
        if ($kind === ClientAccessKinds::KIND_LINK) {
            return self::GROUP_LINK;
        }
        if ($kind === ClientAccessKinds::KIND_TEXT) {
            return self::GROUP_TEXT;
        }
        if ($kind === ClientAccessKinds::KIND_IMAGE) {
            return self::GROUP_MEDIA;
        }

        return self::GROUP_OTHER;
    }

    private function renderGroup(string $label, string $icon, string $fieldsHtml): string
    {
        return '<div class="admin-edit-group">'
            . '<h3 class="admin-edit-group__title">'
            . '<i class="fa-solid ' . htmlspecialchars($icon, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" aria-hidden="true"></i> '
            . htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            . '</h3>'
            . '<div class="admin-edit-group__fields">' . $fieldsHtml . '</div>'
            . '</div>';
    }

    /**
     * @param array<string, mixed> $field
     * @param array<string, mixed> $content
     */
    private function renderField(
        string $sectionId,
        string $prefix,
        string $key,
        array $field,
        array $content,
        bool $mediasEnabled,
        string $variant = '',
    ): string {
        $type = (string) ($field['type'] ?? 'text');
        $label = (string) ($field['label'] ?? $key);

        if ($type === 'buttons') {
            return $this->renderButtons($sectionId, $prefix, $label, $content);
        }
        if ($type === 'repeater') {
            return $this->renderRepeater($sectionId, $prefix, $key, $label, $field, $content, $mediasEnabled, $variant);
        }
        if ($type === 'image' || $type === 'video') {
            return $this->renderMedia($sectionId, $prefix, $key, $label, $type, $content, $mediasEnabled);
        }
        if ($type === 'textarea') {
            return $this->input($sectionId, $prefix, $key, $label, (string) ($content[$key] ?? ''), 'textarea');
        }
        if ($type === 'select') {
            return $this->renderSelect($sectionId, $prefix, $key, $label, $field, (string) ($content[$key] ?? ''));
        }
        if ($type === 'url' || str_contains($key, 'href') || str_contains($key, 'link')) {
            $id = 's' . preg_replace('/[^a-zA-Z0-9_-]/', '', $sectionId) . '-' . $key;

            return $this->linkField($id, $prefix . 'content_' . $key, $label, (string) ($content[$key] ?? ''));
        }

        return $this->input($sectionId, $prefix, $key, $label, (string) ($content[$key] ?? ''), 'text');
    }

    /**
     * @param array<string, mixed> $content
     */
    private function renderButtons(string $sectionId, string $prefix, string $label, array $content): string
    {
        $buttons = is_array($content['buttons'] ?? null) ? array_values($content['buttons']) : [];
        $rows = [];
        foreach ($buttons as $i => $button) {
            $rows[] = $this->buttonCard($sectionId, $prefix, (string) $i, (int) $i + 1, is_array($button) ? $button : []);
        }
        if ($rows === []) {
            $rows[] = $this->buttonCard($sectionId, $prefix, '0', 1, []);
        }

        return '<div class="admin-edit-buttons" role="group" aria-label="' . htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">'
            . '<input type="hidden" name="' . htmlspecialchars($prefix . 'content_buttons_count', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            . '" value="' . count($rows) . '" />'
            . implode('', $rows)
            . '</div>';
    }

    /**
     * @param array<string, mixed> $button
     */
    private function buttonCard(string $sectionId, string $prefix, string $index, int $number, array $button): string
    {
        $label = (string) ($button['label'] ?? '');
        $href = (string) ($button['href'] ?? '');
        $style = SectionButtonStyle::normalize((string) ($button['style'] ?? 'primary'));
        $domId = 's' . preg_replace('/[^a-zA-Z0-9_-]/', '', $sectionId) . '-btn' . $index;

        return '<article class="admin-button-card">'
            . '<p class="admin-button-card__title">Bouton ' . $number . '</p>'
            . '<div class="admin-button-card__stack">'
            . $this->namedInput($domId . '-label', $prefix . 'content_buttons_' . $index . '_label', 'Texte du bouton', $label, 'text')
            . $this->linkField($domId . '-href', $prefix . 'content_buttons_' . $index . '_href', 'Lien', $href)
            . '<div class="admin-field"><label class="admin-label" for="' . htmlspecialchars($domId . '-style', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">Apparence</label>'
            . '<select class="admin-input" id="' . htmlspecialchars($domId . '-style', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            . '" name="' . htmlspecialchars($prefix . 'content_buttons_' . $index . '_style', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">'
            . '<option value="primary"' . ($style === 'primary' ? ' selected' : '') . '>Principal</option>'
            . '<option value="secondary"' . ($style === 'secondary' ? ' selected' : '') . '>Secondaire</option>'
            . '<option value="outline"' . ($style === 'outline' ? ' selected' : '') . '>Contour</option>'
            . '</select></div>'
            . '</div></article>';
    }

    private function linkField(string $id, string $name, string $label, string $value): string
    {
        $safeId = htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeName = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeValue = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<div class="admin-field admin-field--full">'
            . '<label class="admin-label" for="' . $safeId . '">' . $safeLabel . '</label>'
            . '<div class="admin-link-picker" data-link-picker>'
            . '<select class="admin-input" aria-label="Choisir une page du site" data-link-picker-select>'
            . $this->linkOptionsHtml()
            . '</select>'
            . '<input class="admin-input" type="text" id="' . $safeId . '" name="' . $safeName . '" value="' . $safeValue
            . '" placeholder="/contact ou https://…" data-link-picker-input />'
            . '</div>'
            . '<p class="admin-hint">Choisissez une page, ou saisissez une adresse libre.</p>'
            . '</div>';
    }

    private function linkOptionsHtml(): string
    {
        if ($this->linkOptionsCache === null) {
            $this->linkOptionsCache = LinkPicker::buildSelectOptions($this->pages);
        }

        return $this->linkOptionsCache;
    }

    /**
     * @param array<string, mixed> $field
     * @param array<string, mixed> $content
     */
    private function renderRepeater(
        string $sectionId,
        string $prefix,
        string $key,
        string $label,
        array $field,
        array $content,
        bool $mediasEnabled,
        string $variant = '',
    ): string {
        $subFields = is_array($field['fields'] ?? null) ? $field['fields'] : [];
        $visibleSubFields = [];
        foreach ($subFields as $subKey => $subDef) {
            if (!is_string($subKey) || !is_array($subDef)) {
                continue;
            }
            if ($variant !== '' && !$this->fieldSchema->fieldAppliesToVariant($subDef, $variant)) {
                continue;
            }
            $visibleSubFields[$subKey] = $subDef;
        }
        $subFields = $this->orderRepeaterSubFields($visibleSubFields);
        $items = is_array($content[$key] ?? null) ? array_values($content[$key]) : [];
        if ($items === []) {
            $items = [[]];
        }

        $rows = [];
        foreach ($items as $i => $item) {
            if (!is_array($item)) {
                $item = [];
            }
            $itemTitle = trim((string) ($item['title'] ?? ''));
            $cardTitle = $itemTitle !== ''
                ? $itemTitle
                : 'Élément ' . ((int) $i + 1);

            $cells = [];
            foreach ($subFields as $subKey => $subDef) {
                if (!is_array($subDef)) {
                    continue;
                }
                $subLabel = (string) ($subDef['label'] ?? $subKey);
                $subType = (string) ($subDef['type'] ?? 'text');
                $name = $prefix . 'content_' . $key . '_' . $i . '_' . $subKey;
                $id = 's' . preg_replace('/[^a-zA-Z0-9_-]/', '', $sectionId) . '-' . $key . '-' . $i . '-' . $subKey;
                $value = (string) ($item[$subKey] ?? '');
                if (($subType === 'image' || $subType === 'video') && $mediasEnabled) {
                    $cells[] = $this->mediaPicker($id, $name, $subLabel, $subType, $value);
                } elseif ($subType === 'url' || str_contains((string) $subKey, 'href') || str_contains((string) $subKey, 'link')) {
                    $cells[] = $this->linkField($id, $name, $subLabel, $value);
                } else {
                    $inputType = $subType === 'textarea' ? 'textarea' : 'text';
                    $cells[] = $this->namedInput($id, $name, $subLabel, $value, $inputType);
                }
            }
            $rows[] = '<article class="admin-list-card">'
                . '<p class="admin-list-card__title">'
                . '<span class="admin-list-card__index">' . ((int) $i + 1) . '</span> '
                . htmlspecialchars($cardTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                . '</p>'
                . '<div class="admin-list-card__grid">' . implode('', $cells) . '</div>'
                . '</article>';
        }

        return '<div class="admin-edit-list" role="group" aria-label="' . htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">'
            . '<p class="admin-hint">' . htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            . ' : ce que vos visiteurs voient dans ce bloc.</p>'
            . implode('', $rows)
            . '</div>';
    }

    /**
     * @param array<string, mixed> $subFields
     *
     * @return array<string, mixed>
     */
    private function orderRepeaterSubFields(array $subFields): array
    {
        $priority = ['title' => 0, 'text' => 1, 'label' => 2, 'url' => 3, 'href' => 4];
        $keys = array_keys($subFields);
        usort($keys, static function (string|int $a, string|int $b) use ($priority): int {
            $ka = (string) $a;
            $kb = (string) $b;
            $pa = $priority[$ka] ?? 50;
            $pb = $priority[$kb] ?? 50;
            if ($pa !== $pb) {
                return $pa <=> $pb;
            }

            return strcmp($ka, $kb);
        });

        $ordered = [];
        foreach ($keys as $key) {
            $ordered[$key] = $subFields[$key];
        }

        return $ordered;
    }

    /**
     * @param array<string, mixed> $content
     */
    private function renderMedia(
        string $sectionId,
        string $prefix,
        string $key,
        string $label,
        string $kind,
        array $content,
        bool $mediasEnabled,
    ): string {
        $value = (string) ($content[$key] ?? '');
        $id = 's' . preg_replace('/[^a-zA-Z0-9_-]/', '', $sectionId) . '-' . $key;
        $name = $prefix . 'content_' . $key;

        if ($mediasEnabled) {
            return $this->mediaPicker($id, $name, $label, $kind, $value);
        }

        return $this->namedInput($id, $name, $label, $value, 'url')
            . '<p class="admin-hint">Collez l\'adresse de l\'image ou de la vidéo.</p>';
    }

    private function mediaPicker(
        string $id,
        string $name,
        string $label,
        string $kind,
        string $value,
    ): string {
        $urls = $kind === 'video'
            ? $this->clientMediaUrls('video')
            : $this->clientMediaUrls('image');

        $safeId = htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeName = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeValue = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $picks = [];
        $seen = [];

        if ($value !== '') {
            $picks[] = $this->mediaPickButton($value, $kind, true);
            $seen[$value] = true;
        }

        foreach ($urls as $url) {
            if (isset($seen[$url])) {
                continue;
            }
            $picks[] = $this->mediaPickButton($url, $kind, $url === $value);
            $seen[$url] = true;
        }

        $grid = $picks === []
            ? '<p class="admin-media-picker__empty">Aucune image dans votre bibliothèque.</p>'
            : '<div class="admin-media-picker__grid" role="listbox" aria-label="' . $safeLabel . '">'
                . implode('', $picks)
                . '</div>';

        $noneSelected = $value === '' ? ' is-selected' : '';

        return '<div class="admin-field admin-media-picker" data-admin-media-picker>'
            . '<span class="admin-label" id="' . $safeId . '-label">' . $safeLabel . '</span>'
            . '<input type="hidden" id="' . $safeId . '" name="' . $safeName . '" value="' . $safeValue . '" data-admin-media-value />'
            . $grid
            . '<div class="admin-media-picker__actions">'
            . '<button type="button" class="admin-btn admin-btn--ghost admin-btn--sm' . $noneSelected . '" data-admin-media-clear'
            . ' aria-pressed="' . ($value === '' ? 'true' : 'false') . '">Aucune image</button>'
            . '<a class="admin-btn admin-btn--soft admin-btn--sm" href="/admin/medias">'
            . '<i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i> Importer</a>'
            . '</div>'
            . '</div>';
    }

    private function mediaPickButton(string $url, string $kind, bool $selected): string
    {
        $safeUrl = htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $name = htmlspecialchars(basename($url), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $selectedClass = $selected ? ' is-selected' : '';
        $ariaSelected = $selected ? 'true' : 'false';

        if ($kind === 'video') {
            $thumb = '<span class="admin-media-picker__thumb admin-media-picker__thumb--video" aria-hidden="true">'
                . '<i class="fa-solid fa-film"></i></span>';
        } else {
            $thumb = '<img class="admin-media-picker__thumb" src="' . $safeUrl . '" alt="" width="96" height="72" loading="lazy" />';
        }

        return '<button type="button" class="admin-media-picker__pick' . $selectedClass . '" role="option"'
            . ' data-admin-media-pick data-url="' . $safeUrl . '"'
            . ' aria-selected="' . $ariaSelected . '"'
            . ' aria-label="Choisir ' . $name . '" title="' . $name . '">'
            . $thumb
            . '</button>';
    }

    /**
     * @return list<string>
     */
    private function clientMediaUrls(string $kind): array
    {
        $urls = [];
        foreach ($this->media->all($kind, MediaRepository::OWNER_CLIENT) as $record) {
            $url = (string) ($record['url'] ?? '');
            if ($url === '') {
                continue;
            }
            $urls[] = $url;
        }

        return $urls;
    }

    /**
     * @param array<string, mixed> $field
     */
    private function renderSelect(
        string $sectionId,
        string $prefix,
        string $key,
        string $label,
        array $field,
        string $value,
    ): string {
        $raw = $field['options'] ?? [];
        $options = [];
        if (is_string($raw)) {
            $trimmed = trim($raw, "[] \t\n\r");
            foreach (explode(',', $trimmed) as $opt) {
                $opt = trim($opt);
                if ($opt !== '') {
                    $options[$opt] = $opt;
                }
            }
        } elseif (is_array($raw)) {
            foreach ($raw as $k => $v) {
                if (is_string($v)) {
                    $options[is_string($k) ? $k : $v] = $v;
                }
            }
        }

        $id = 's' . preg_replace('/[^a-zA-Z0-9_-]/', '', $sectionId) . '-' . $key;
        $safeId = htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html = '<div class="admin-field"><label class="admin-label" for="' . $safeId . '">'
            . htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</label>'
            . '<select class="admin-input" id="' . $safeId . '" name="'
            . htmlspecialchars($prefix . 'content_' . $key, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">';
        foreach ($options as $optValue => $optLabel) {
            $sel = (string) $optValue === $value ? ' selected' : '';
            $html .= '<option value="' . htmlspecialchars((string) $optValue, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"' . $sel . '>'
                . htmlspecialchars((string) $optLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</option>';
        }
        $html .= '</select></div>';

        return $html;
    }

    private function input(string $sectionId, string $prefix, string $key, string $label, string $value, string $type): string
    {
        $id = 's' . preg_replace('/[^a-zA-Z0-9_-]/', '', $sectionId) . '-' . $key;

        return $this->namedInput($id, $prefix . 'content_' . $key, $label, $value, $type);
    }

    private function namedInput(string $id, string $name, string $label, string $value, string $type): string
    {
        $safeId = htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeName = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeValue = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        if ($type === 'textarea') {
            return '<div class="admin-field admin-field--full"><label class="admin-label" for="' . $safeId . '">' . $safeLabel . '</label>'
                . '<textarea class="admin-input admin-textarea admin-textarea--editor" id="' . $safeId . '" name="' . $safeName
                . '" rows="5">'
                . $safeValue . '</textarea></div>';
        }

        return '<div class="admin-field"><label class="admin-label" for="' . $safeId . '">' . $safeLabel . '</label>'
            . '<input class="admin-input" id="' . $safeId . '" type="' . htmlspecialchars($type, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            . '" name="' . $safeName . '" value="' . $safeValue . '" /></div>';
    }
}
