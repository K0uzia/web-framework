<?php

declare(strict_types=1);

namespace App\Http\Dev;

use Capsule\DevDashboard;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\Http\Support\FormData;
use Capsule\LayoutRegistry;
use Capsule\Page;
use Capsule\PageRepository;
use Capsule\SectionRegistry;

final class PagesController
{
    use DevHx;

    public function __construct(
        private readonly DevDashboard $ui,
        private readonly PageRepository $pages,
        private readonly SectionRegistry $registry,
        private readonly SectionFormRenderer $sectionForms,
        private readonly LayoutRegistry $layouts,
    ) {
    }

    public function index(Request $request): Response
    {
        $pages = $this->pages->all();
        usort($pages, static fn ($a, $b) => strcmp((string) $b->updatedAt, (string) $a->updatedAt));

        $rows = [];
        foreach ($pages as $page) {
            $rows[] = $this->pageRow($page);
        }

        $empty = $pages === [];

        return $this->ui->render('pages-index.html', [
            'title' => 'Pages',
            'crumb_html' => Breadcrumb::render([['label' => 'Pages']]),
            'pages_count' => (string) count($pages),
            'rows_html' => implode('', $rows),
            'is_empty' => $empty,
            'empty_class' => $empty ? '' : 'visually-hidden',
            'table_class' => $empty ? 'visually-hidden' : '',
            'flash' => $this->ui->flashFromRequest($request),
        ]);
    }

    public function createForm(Request $request): Response
    {
        return $this->ui->render('page-new.html', [
            'title' => 'Nouvelle page',
            'crumb_html' => Breadcrumb::render([
                ['label' => 'Pages', 'href' => '/dev/pages'],
                ['label' => 'Nouvelle page'],
            ]),
            'layout_options' => $this->buildLayoutOptions('default'),
            'flash' => $this->ui->flashFromRequest($request),
        ]);
    }

    private function pageRow(Page $page): string
    {
        $slug = SlugCodec::encode($page->slug);
        $status = $page->published
            ? '<span class="dev-badge dev-badge--success"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Publiée</span>'
            : '<span class="dev-badge dev-badge--muted"><i class="fa-solid fa-pen" aria-hidden="true"></i> Brouillon</span>';
        $home = $page->slug === '' ? '<span class="dev-badge dev-badge--info">Accueil</span>' : '';
        $updated = $this->formatDate($page->updatedAt);

        return '<tr data-page-row data-title="' . htmlspecialchars(mb_strtolower($page->title), ENT_QUOTES)
            . '" data-path="' . htmlspecialchars(mb_strtolower($page->routePath()), ENT_QUOTES) . '">'
            . '<td><a class="dev-table__link" href="/dev/pages/' . $slug . '">'
            . '<span class="dev-table__title">' . htmlspecialchars($page->title, ENT_QUOTES) . '</span>'
            . '<span class="dev-table__path">' . htmlspecialchars($page->routePath(), ENT_QUOTES) . '</span>'
            . '</a></td>'
            . '<td>' . $status . ' ' . $home . '</td>'
            . '<td class="dev-table__muted">' . htmlspecialchars($updated, ENT_QUOTES) . '</td>'
            . '<td class="dev-table__actions">'
            . '<a class="dev-icon-btn" href="/dev/pages/' . $slug . '" title="Éditer" aria-label="Éditer ' . htmlspecialchars($page->title, ENT_QUOTES) . '"><i class="fa-solid fa-pen" aria-hidden="true"></i></a>'
            . '<a class="dev-icon-btn" href="' . htmlspecialchars($page->routePath(), ENT_QUOTES) . '" target="_blank" rel="noopener" title="Voir" aria-label="Voir la page"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a>'
            . '<details class="dev-menu">'
            . '<summary class="dev-icon-btn" aria-label="Plus d\'actions"><i class="fa-solid fa-ellipsis-vertical" aria-hidden="true"></i></summary>'
            . '<div class="dev-menu__panel" role="menu">'
            . '<form method="post" action="/dev/pages/' . $slug . '/duplicate" role="none"><button type="submit" role="menuitem"><i class="fa-solid fa-copy" aria-hidden="true"></i> Dupliquer</button></form>'
            . '<form method="post" action="/dev/pages/' . $slug . '/delete" role="none" data-confirm="Supprimer définitivement cette page ?"><button type="submit" role="menuitem" class="dev-menu__danger"><i class="fa-solid fa-trash" aria-hidden="true"></i> Supprimer</button></form>'
            . '</div></details>'
            . '</td></tr>';
    }

    private function formatDate(string $value): string
    {
        if ($value === '') {
            return '-';
        }
        $ts = strtotime($value . ' UTC');
        if ($ts === false) {
            return $value;
        }

        return date('d/m/Y à H:i', $ts);
    }

    public function store(Request $request): Response
    {
        $data = FormData::fromRequest($request);
        $slug = trim($data['slug'] ?? '');
        $title = trim($data['title'] ?? '');

        if ($title === '') {
            $response = $this->ui->redirect('/dev/pages/new');

            return $this->ui->withFlash($response, 'Le titre est requis.');
        }

        $layout = trim($data['layout'] ?? 'default');
        if (!$this->layouts->exists($layout)) {
            $layout = 'default';
        }

        $this->pages->save(new Page(
            slug: $slug,
            title: $title,
            layout: $layout,
            description: trim($data['description'] ?? ''),
            sections: [],
            meta: [],
            published: false,
            updatedAt: '',
        ));

        return $this->ui->redirect('/dev/pages/' . SlugCodec::encode($slug));
    }

    public function edit(Request $request, string $slug = ''): Response
    {
        $page = $this->pages->findBySlug(SlugCodec::decode($slug), false);
        if ($page === null) {
            return $this->ui->redirect('/dev/pages');
        }

        $statusBadge = $page->published
            ? '<span class="dev-badge dev-badge--success"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Publiée</span>'
            : '<span class="dev-badge dev-badge--muted"><i class="fa-solid fa-pen" aria-hidden="true"></i> Brouillon</span>';

        return $this->ui->render('page-edit.html', [
            'title' => 'Éditer : ' . $page->title,
            'crumb_html' => Breadcrumb::render([
                ['label' => 'Pages', 'href' => '/dev/pages'],
                ['label' => $page->title],
            ]),
            'page_slug' => SlugCodec::encode($page->slug),
            'page_path' => $page->routePath(),
            'page_title' => $page->title,
            'page_layout' => $page->layout,
            'page_description' => $page->description,
            'page_published_checked' => $page->published ? 'checked' : '',
            'page_status_badge' => $statusBadge,
            'current_slug_value' => $page->slug,
            'is_home' => $page->slug === '',
            'layout_options' => $this->buildLayoutOptions($page->layout),
            'show_header_options' => $this->buildVisibilityOptions((string) ($page->meta['show_header'] ?? 'default')),
            'show_footer_options' => $this->buildVisibilityOptions((string) ($page->meta['show_footer'] ?? 'default')),
            'preview_url' => '/dev/preview/' . SlugCodec::encode($page->slug),
            'sections_html' => $this->sectionForms->renderAll($page),
            'sections_count' => count($page->sections),
            'block_picker_html' => $this->buildBlockPickerHtml(),
            'rename_section_html' => $this->buildRenameSectionHtml($page),
            'flash' => $this->ui->flashFromRequest($request),
        ]);
    }

    private function buildVisibilityOptions(string $current): string
    {
        $choices = [
            'default' => 'Réglage du site (par défaut)',
            'show' => 'Toujours afficher sur cette page',
            'hide' => 'Toujours masquer sur cette page',
        ];

        $options = [];
        foreach ($choices as $value => $label) {
            $selected = $value === $current ? ' selected' : '';
            $options[] = '<option value="' . $value . '"' . $selected . '>' . htmlspecialchars($label, ENT_QUOTES) . '</option>';
        }

        return implode('', $options);
    }

    private function buildRenameSectionHtml(Page $page): string
    {
        if ($page->slug === '') {
            return '<p class="dev-hint">La page d\'accueil utilise toujours l\'adresse racine <code>/</code>.</p>';
        }

        $slug = SlugCodec::encode($page->slug);

        return '<form method="post" action="/dev/pages/' . $slug . '/rename" class="dev-form--grid dev-form--grid-2" data-confirm="Changer l\'adresse de cette page ?">'
            . '<div class="dev-field dev-form__full">'
            . '<label class="dev-label" for="new_slug">Nouvelle adresse</label>'
            . '<input class="dev-input" id="new_slug" type="text" name="new_slug" value="' . htmlspecialchars($page->slug, ENT_QUOTES) . '" pattern="[a-z0-9]+(-[a-z0-9]+)*" />'
            . '<span class="dev-hint">Lettres minuscules, chiffres et tirets uniquement. Les liens existants pointant vers l\'ancienne adresse casseront.</span>'
            . '</div>'
            . '<div class="dev-form__full">'
            . '<button type="submit" class="dev-button dev-button--secondary">Mettre à jour l\'adresse</button>'
            . '</div></form>';
    }

    private function buildBlockPickerHtml(): string
    {
        $icons = [
            'hero' => 'fa-solid fa-panorama',
            'features' => 'fa-solid fa-table-cells-large',
            'cta' => 'fa-solid fa-bullhorn',
        ];
        $descriptions = [
            'hero' => 'Grand titre d\'introduction avec accroche et bouton d\'action.',
            'features' => 'Grille de points clés avec titre et texte.',
            'cta' => 'Bandeau d\'appel à l\'action avec bouton.',
        ];

        $cards = [];
        foreach ($this->registry->getTypes() as $type) {
            $def = $this->registry->getTypeDefinition($type);
            $label = is_string($def['label'] ?? null) ? $def['label'] : $type;
            $icon = $icons[$type] ?? 'fa-solid fa-square';
            $desc = $descriptions[$type] ?? '';

            $cards[] = '<button type="button" class="dev-block-card" data-block-type="' . htmlspecialchars($type, ENT_QUOTES) . '">'
                . '<span class="dev-block-card__icon" aria-hidden="true"><i class="' . $icon . '"></i></span>'
                . '<span class="dev-block-card__title">' . htmlspecialchars($label, ENT_QUOTES) . '</span>'
                . '<span class="dev-block-card__desc">' . htmlspecialchars($desc, ENT_QUOTES) . '</span>'
                . '</button>';
        }

        return implode('', $cards);
    }

    public function update(Request $request, string $slug = ''): Response
    {
        $page = $this->pages->findBySlug(SlugCodec::decode($slug), false);
        if ($page === null) {
            return $this->ui->redirect('/dev/pages');
        }

        $data = FormData::fromRequest($request);
        $layout = trim($data['layout'] ?? $page->layout);
        if (!$this->layouts->exists($layout)) {
            $layout = $page->layout;
        }

        $meta = $page->meta;
        if (isset($data['show_header'])) {
            $meta['show_header'] = $this->normalizeVisibility($data['show_header']);
        }
        if (isset($data['show_footer'])) {
            $meta['show_footer'] = $this->normalizeVisibility($data['show_footer']);
        }

        $this->pages->save(new Page(
            slug: $page->slug,
            title: trim($data['title'] ?? $page->title),
            layout: $layout,
            description: trim($data['description'] ?? $page->description),
            sections: $page->sections,
            meta: $meta,
            published: ($data['published'] ?? '0') === '1',
            updatedAt: $page->updatedAt,
        ));

        if ($this->isHx($request)) {
            return $this->ui->partial('saved.html', ['message' => 'Page enregistrée']);
        }

        return $this->ui->withFlash(
            $this->ui->redirect('/dev/pages/' . SlugCodec::encode($page->slug)),
            'Page enregistrée.',
        );
    }

    public function destroy(Request $request, string $slug = ''): Response
    {
        $decoded = SlugCodec::decode($slug);
        if ($decoded !== '') {
            $this->pages->delete($decoded);
        }

        return $this->ui->withFlash($this->ui->redirect('/dev/pages'), 'Page supprimée.');
    }

    public function duplicate(Request $request, string $slug = ''): Response
    {
        $page = $this->pages->findBySlug(SlugCodec::decode($slug), false);
        if ($page === null) {
            return $this->ui->redirect('/dev/pages');
        }

        $base = $page->slug === '' ? 'accueil' : $page->slug;
        $newSlug = $this->uniqueSlug($base . '-copie');

        $this->pages->save(new Page(
            slug: $newSlug,
            title: $page->title . ' (copie)',
            layout: $page->layout,
            description: $page->description,
            sections: $page->sections,
            meta: $page->meta,
            published: false,
            updatedAt: '',
        ));

        return $this->ui->withFlash(
            $this->ui->redirect('/dev/pages/' . SlugCodec::encode($newSlug)),
            'Page dupliquée en brouillon.',
        );
    }

    public function rename(Request $request, string $slug = ''): Response
    {
        $page = $this->pages->findBySlug(SlugCodec::decode($slug), false);
        if ($page === null) {
            return $this->ui->redirect('/dev/pages');
        }

        $data = FormData::fromRequest($request);
        $newSlug = trim($data['new_slug'] ?? '', " \t\n\r\0\x0B/");
        $newSlug = strtolower($newSlug);

        if ($newSlug === $page->slug) {
            return $this->ui->redirect('/dev/pages/' . SlugCodec::encode($page->slug));
        }

        if ($newSlug !== '' && !preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $newSlug)) {
            return $this->ui->withFlash(
                $this->ui->redirect('/dev/pages/' . SlugCodec::encode($page->slug)),
                'Adresse invalide : lettres minuscules, chiffres et tirets uniquement.',
            );
        }

        if ($this->pages->findBySlug($newSlug, false) !== null) {
            return $this->ui->withFlash(
                $this->ui->redirect('/dev/pages/' . SlugCodec::encode($page->slug)),
                'Cette adresse est déjà utilisée par une autre page.',
            );
        }

        $this->pages->save(new Page(
            slug: $newSlug,
            title: $page->title,
            layout: $page->layout,
            description: $page->description,
            sections: $page->sections,
            meta: $page->meta,
            published: $page->published,
            updatedAt: '',
        ));
        $this->pages->delete($page->slug);

        return $this->ui->withFlash(
            $this->ui->redirect('/dev/pages/' . SlugCodec::encode($newSlug)),
            'Adresse mise à jour.',
        );
    }

    private function normalizeVisibility(string $value): string
    {
        return in_array($value, ['show', 'hide'], true) ? $value : 'default';
    }

    private function uniqueSlug(string $base): string
    {
        $slug = $base;
        $i = 2;
        while ($this->pages->findBySlug($slug, false) !== null) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    private function buildLayoutOptions(string $current): string
    {
        $options = [];
        foreach ($this->layouts->all() as $layout) {
            $selected = $layout === $current ? ' selected' : '';
            $options[] = '<option value="' . htmlspecialchars($layout, ENT_QUOTES) . '"' . $selected . '>'
                . htmlspecialchars($layout, ENT_QUOTES) . '</option>';
        }

        return implode('', $options);
    }
}
