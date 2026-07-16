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

        return $this->ui->render('client-dashboard.html', [
            'title' => 'Dashboard client',
            'crumb_html' => Breadcrumb::render([['label' => 'Dashboard client']]),
            'medias_checked' => $mediasOn ? ' checked' : '',
            'tree_html' => $this->buildTreeHtml($config),
            'flash' => $this->ui->flashFromRequest($request),
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
     */
    private function buildTreeHtml(array $config): string
    {
        $pages = $this->pages->all();
        if ($pages === []) {
            return '<p class="dev-hint">Aucune page. Créez des pages avant d\'autoriser du contenu.</p>';
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

        foreach ($page->sections as $section) {
            if (!is_array($section)) {
                continue;
            }
            $sectionHtml = $this->renderSectionBlock($slug, $section, $config);
            if ($sectionHtml !== '') {
                $sectionsHtml[] = $sectionHtml;
            }
        }

        $body = $sectionsHtml === []
            ? '<p class="dev-hint">Aucun champ client-éditable sur cette page.</p>'
            : '<div class="dev-perm-sections">' . implode('', $sectionsHtml) . '</div>';

        $title = htmlspecialchars($page->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $pathEsc = htmlspecialchars($pathLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $checked = $pageChecked ? ' checked' : '';
        $open = $pageChecked ? ' open' : '';
        $count = count($sectionsHtml);
        $meta = $count === 0 ? 'aucun bloc' : ($count === 1 ? '1 bloc' : $count . ' blocs');

        return '<details class="dev-perm-page"' . $open . ' data-dev-perm-page>'
            . '<summary class="dev-perm-summary">'
            . '<span class="dev-perm-chevron" aria-hidden="true"><i class="fa-solid fa-chevron-right"></i></span>'
            . '<span class="dev-perm-check-wrap" data-dev-perm-check>'
            . '<input type="checkbox" class="dev-perm-check" data-dev-perm-level="page"'
            . ' aria-label="Tout autoriser sur ' . $title . '"' . $checked . ' />'
            . '</span>'
            . '<span class="dev-perm-summary__main">'
            . '<span class="dev-perm-summary__title">' . $title . '</span>'
            . '<span class="dev-perm-summary__meta">' . $pathEsc . ' · ' . htmlspecialchars($meta, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span>'
            . '</span>'
            . '</summary>'
            . '<div class="dev-perm-page__body">' . $body . '</div>'
            . '</details>';
    }

    /**
     * @param array<string, mixed> $section
     * @param array{pages: array<string, array{sections: array<string, array{fields: list<string>}>}>} $config
     */
    private function renderSectionBlock(string $slug, array $section, array $config): string
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
            $name = htmlspecialchars(
                ClientDashboardConfig::formFieldKey($slug, $sectionId, $fieldKey),
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8',
            );
            $fieldChecked = in_array($fieldKey, $allowed, true) ? ' checked' : '';
            $fieldItems[] = '<label class="dev-perm-field">'
                . '<input type="checkbox" class="dev-perm-check" name="' . $name . '" value="1"'
                . ' data-dev-perm-level="field"' . $fieldChecked . ' />'
                . '<span>' . htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span>'
                . '</label>';
        }

        $heading = htmlspecialchars($typeLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $variantEsc = htmlspecialchars($variantLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $checked = $sectionChecked ? ' checked' : '';
        $open = $sectionChecked ? ' open' : '';
        $meta = count($allowed) . ' / ' . count($fields) . ' champs';

        return '<details class="dev-perm-section"' . $open . ' data-dev-perm-section>'
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
