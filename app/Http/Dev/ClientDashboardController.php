<?php

declare(strict_types=1);

namespace App\Http\Dev;

use Capsule\ClientDashboardConfig;
use Capsule\DevDashboard;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\Http\Support\FormData;
use Capsule\Page;
use Capsule\PageRepository;
use Capsule\Section\SectionFieldSchema;
use Capsule\SectionRegistry;
use Capsule\SiteRepository;

final class ClientDashboardController
{
    use DevHx;

    public function __construct(
        private readonly DevDashboard $ui,
        private readonly SiteRepository $site,
        private readonly PageRepository $pages,
        private readonly SectionRegistry $registry,
        private readonly SectionFieldSchema $fieldSchema,
    ) {
    }

    public function edit(Request $request): Response
    {
        $config = $this->site->getClientDashboard();
        $mediasOn = ClientDashboardConfig::isMediasEnabled($config);
        $siteOn = ClientDashboardConfig::isSiteEnabled($config);
        $stats = $this->computeStats($config);
        $allPages = $this->pages->all();
        $configured = $stats['pages'] > 0 || $mediasOn || $siteOn;

        return $this->ui->render('client-dashboard.html', [
            'title' => 'Dashboard client',
            'crumb_html' => Breadcrumb::render([['label' => 'Espace client'], ['label' => 'Configuration']]),
            'medias_checked' => $mediasOn ? ' checked' : '',
            'site_checked' => $siteOn ? ' checked' : '',
            'tree_html' => $this->buildTreeHtml($config),
            'flash' => $this->ui->flashFromRequest($request),
            'stat_pages' => (string) $stats['pages'],
            'stat_fields' => (string) $stats['fields'],
            'stat_medias' => $mediasOn ? 'Oui' : 'Non',
            'stat_site' => $siteOn ? 'Oui' : 'Non',
            'status_label' => $configured ? 'Prêt' : 'Non configuré',
            'status_badge_class' => $configured ? 'dev-cd__badge--ready' : 'dev-cd__badge--idle',
            'setup_empty_class' => $configured ? 'hidden' : '',
            'step1_class' => $allPages !== [] ? 'is-done' : '',
            'step2_class' => $stats['pages'] > 0 || $siteOn ? 'is-done' : '',
            'step3_class' => $configured ? 'is-done' : '',
            'pages_available' => (string) count($allPages),
        ], section: 'client');
    }

    public function update(Request $request): Response
    {
        $data = FormData::fromRequest($request);
        $index = $this->buildSectionIndex();
        $slugs = array_map(static fn (Page $page): string => $page->slug, $this->pages->all());
        $config = ClientDashboardConfig::fromFormData($data, $index, $slugs);
        $this->site->setClientDashboard($config);

        if ($this->isHx($request)) {
            return $this->ui->partial('saved.html', ['message' => 'Permissions enregistrées']);
        }

        return $this->ui->withFlash(
            $this->ui->redirect('/dev/client-dashboard'),
            'Permissions enregistrées.',
        );
    }

    /**
     * @param array{medias_enabled?: bool, pages: array<string, array{sections: array<string, array{fields: list<string>}>}>} $config
     *
     * @return array{pages: int, fields: int}
     */
    private function computeStats(array $config): array
    {
        $pages = 0;
        $fields = 0;
        foreach ($config['pages'] as $page) {
            if (!is_array($page)) {
                continue;
            }
            $sections = $page['sections'] ?? [];
            if (!is_array($sections) || $sections === []) {
                continue;
            }
            $pages++;
            foreach ($sections as $section) {
                if (is_array($section) && is_array($section['fields'] ?? null)) {
                    $fields += count($section['fields']);
                }
            }
        }

        return ['pages' => $pages, 'fields' => $fields];
    }

    /**
     * @param array{medias_enabled?: bool, pages: array<string, array{sections: array<string, array{fields: list<string>}>}>} $config
     */
    private function buildTreeHtml(array $config): string
    {
        $pages = $this->pages->all();
        if ($pages === []) {
            return '<div class="dev-cd__tree-empty">'
                . '<p class="dev-hint">Aucune page dans le site.</p>'
                . '<a class="dev-button" href="/dev/pages"><i class="fa-solid fa-plus" aria-hidden="true"></i> Créer une page</a>'
                . '</div>';
        }

        $parts = [];
        foreach ($pages as $page) {
            $parts[] = $this->renderPageBlock($page, $config);
        }

        return '<div class="dev-perm-tree" data-dev-perm-tree>' . implode('', $parts) . '</div>';
    }

    /**
     * @param array{pages: array<string, array{sections: array<string, array{fields: list<string>}>}>} $config
     */
    private function renderPageBlock(Page $page, array $config): string
    {
        $slug = $page->slug;
        $pathLabel = $slug === '' ? '/' : '/' . $slug;
        $pageChecked = ClientDashboardConfig::isPageEditable($config, $slug);
        $sectionsHtml = [];
        $fieldTotal = 0;
        $fieldAllowed = 0;

        foreach ($page->sections as $section) {
            if (!is_array($section)) {
                continue;
            }
            $sectionHtml = $this->renderSectionBlock($slug, $section, $config);
            if ($sectionHtml === '') {
                continue;
            }
            $sectionsHtml[] = $sectionHtml['html'];
            $fieldTotal += $sectionHtml['total'];
            $fieldAllowed += $sectionHtml['allowed'];
        }

        $body = $sectionsHtml === []
            ? '<p class="dev-hint">Aucun champ client-éditable sur cette page (ajoutez des blocs avec du contenu).</p>'
            : '<div class="dev-perm-sections">' . implode('', $sectionsHtml) . '</div>';

        $title = htmlspecialchars($page->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $pathEsc = htmlspecialchars($pathLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $checked = $pageChecked ? ' checked' : '';
        $open = $pageChecked ? ' open' : '';
        $slugEnc = rawurlencode($slug === '' ? '_' : $slug);
        $status = $pageChecked
            ? '<span class="dev-cd__page-status is-on">' . $fieldAllowed . ' / ' . $fieldTotal . ' champs</span>'
            : '<span class="dev-cd__page-status">Fermée</span>';

        return '<details class="dev-perm-page"' . $open . ' data-dev-perm-page>'
            . '<summary class="dev-perm-summary">'
            . '<span class="dev-perm-chevron" aria-hidden="true"><i class="fa-solid fa-chevron-right"></i></span>'
            . '<span class="dev-perm-check-wrap" data-dev-perm-check>'
            . '<input type="checkbox" class="dev-perm-check" data-dev-perm-level="page"'
            . ' aria-label="Tout autoriser sur ' . $title . '"' . $checked . ' />'
            . '</span>'
            . '<span class="dev-perm-summary__main">'
            . '<span class="dev-perm-summary__title">' . $title . '</span>'
            . '<span class="dev-perm-summary__meta">' . $pathEsc . '</span>'
            . '</span>'
            . $status
            . '<a class="dev-cd__page-edit" href="/dev/pages/' . $slugEnc . '" title="Éditer la page dans le builder" aria-label="Éditer ' . $title . ' dans le builder" onclick="event.stopPropagation()">'
            . '<i class="fa-solid fa-pen" aria-hidden="true"></i>'
            . '</a>'
            . '</summary>'
            . '<div class="dev-perm-page__body">' . $body . '</div>'
            . '</details>';
    }

    /**
     * @param array<string, mixed> $section
     * @param array{pages: array<string, array{sections: array<string, array{fields: list<string>}>}>} $config
     *
     * @return array{html: string, total: int, allowed: int}|string
     */
    private function renderSectionBlock(string $slug, array $section, array $config): array|string
    {
        $sectionId = is_string($section['id'] ?? null) ? $section['id'] : '';
        $type = is_string($section['type'] ?? null) ? $section['type'] : '';
        $variant = is_string($section['variant'] ?? null) ? $section['variant'] : '';
        if ($sectionId === '' || $type === '') {
            return '';
        }

        $fields = $this->clientEditableFieldsFor($type, $variant);
        if ($fields === []) {
            return '';
        }

        $allowed = ClientDashboardConfig::allowedFields($config, $slug, $sectionId);
        $sectionChecked = $allowed !== [];
        $typeDef = $this->registry->getTypeDefinition($type);
        $typeLabel = is_string($typeDef['label'] ?? null) ? $typeDef['label'] : $type;
        $variants = $this->registry->getVariants($type);
        $variantLabel = is_string($variants[$variant]['label'] ?? null)
            ? $variants[$variant]['label']
            : $variant;

        $fieldItems = [];
        foreach ($fields as $fieldKey => $fieldDef) {
            $label = is_string($fieldDef['label'] ?? null) ? $fieldDef['label'] : $fieldKey;
            $kind = $this->fieldKindLabel($fieldDef);
            $name = htmlspecialchars(
                ClientDashboardConfig::formFieldKey($slug, $sectionId, $fieldKey),
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8',
            );
            $fieldChecked = in_array($fieldKey, $allowed, true) ? ' checked' : '';
            $fieldItems[] = '<label class="dev-perm-field">'
                . '<input type="checkbox" class="dev-perm-check" name="' . $name . '" value="1"'
                . ' data-dev-perm-level="field"' . $fieldChecked . ' />'
                . '<span class="dev-perm-field__text">'
                . '<span class="dev-perm-field__label">' . htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span>'
                . '<span class="dev-perm-field__kind">' . htmlspecialchars($kind, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span>'
                . '</span>'
                . '</label>';
        }

        $heading = htmlspecialchars($typeLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $variantEsc = htmlspecialchars($variantLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $checked = $sectionChecked ? ' checked' : '';
        $open = $sectionChecked ? ' open' : '';
        $meta = count($allowed) . ' / ' . count($fields) . ' champs';

        $html = '<details class="dev-perm-section"' . $open . ' data-dev-perm-section>'
            . '<summary class="dev-perm-summary">'
            . '<span class="dev-perm-chevron" aria-hidden="true"><i class="fa-solid fa-chevron-right"></i></span>'
            . '<span class="dev-perm-check-wrap" data-dev-perm-check>'
            . '<input type="checkbox" class="dev-perm-check" data-dev-perm-level="section"'
            . ' aria-label="Tout autoriser sur ' . $heading . '"' . $checked . ' />'
            . '</span>'
            . '<span class="dev-perm-summary__main">'
            . '<span class="dev-perm-summary__title">' . $heading . '</span>'
            . '<span class="dev-perm-summary__meta">' . $variantEsc . ' · ' . htmlspecialchars($meta, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span>'
            . '</span>'
            . '</summary>'
            . '<div class="dev-perm-fields">' . implode('', $fieldItems) . '</div>'
            . '</details>';

        return [
            'html' => $html,
            'total' => count($fields),
            'allowed' => count($allowed),
        ];
    }

    /**
     * @param array<string, mixed> $fieldDef
     */
    private function fieldKindLabel(array $fieldDef): string
    {
        $type = (string) ($fieldDef['type'] ?? 'text');

        return match ($type) {
            'textarea' => 'Texte long',
            'image' => 'Image',
            'video' => 'Vidéo',
            'url' => 'Lien',
            'buttons' => 'Boutons',
            'repeater' => 'Liste',
            'select' => 'Liste',
            default => 'Texte',
        };
    }

    /**
     * @return array<string, array{type: string, fields: list<string>}>
     */
    private function buildSectionIndex(): array
    {
        $index = [];
        foreach ($this->pages->all() as $page) {
            foreach ($page->sections as $section) {
                if (!is_array($section)) {
                    continue;
                }
                $sectionId = is_string($section['id'] ?? null) ? $section['id'] : '';
                $type = is_string($section['type'] ?? null) ? $section['type'] : '';
                $variant = is_string($section['variant'] ?? null) ? $section['variant'] : '';
                if ($sectionId === '' || $type === '') {
                    continue;
                }
                $index[$sectionId] = [
                    'type' => $type,
                    'fields' => array_keys($this->clientEditableFieldsFor($type, $variant)),
                ];
            }
        }

        return $index;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function clientEditableFieldsFor(string $type, string $variant): array
    {
        $client = $this->registry->getClientEditableFields($type);
        if ($client === []) {
            return [];
        }
        $forVariant = $this->fieldSchema->contentFieldsForVariant($type, $variant);

        return array_intersect_key($client, $forVariant);
    }
}
