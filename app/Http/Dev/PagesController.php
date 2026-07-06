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
use Capsule\SiteRepository;

final class PagesController
{
    use DevHx;

    public function __construct(
        private readonly DevDashboard $ui,
        private readonly PageRepository $pages,
        private readonly SiteRepository $site,
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
            'layout_options' => $this->buildLayoutOptions('default'),
            'page_template_options' => PageTemplates::buildOptionsHtml(),
            'flash' => $this->ui->flashFromRequest($request),
        ]);
    }

    public function createForm(Request $request): Response
    {
        return $this->ui->redirect('/dev/pages#new');
    }

    private function pageRow(Page $page): string
    {
        $slug = SlugCodec::encode($page->slug);
        $status = $page->published
            ? '<span class="dev-badge dev-badge--success"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Publiée</span>'
            : '<span class="dev-badge dev-badge--muted"><i class="fa-solid fa-pen" aria-hidden="true"></i> Brouillon</span>';
        $home = $page->slug === '' ? '<span class="dev-badge dev-badge--info">Accueil</span>' : '';
        $updated = $this->formatDate($page->updatedAt);

        $menuActions = '<form method="post" action="/dev/pages/' . $slug . '/duplicate" role="none"><button type="submit" role="menuitem"><i class="fa-solid fa-copy" aria-hidden="true"></i> Dupliquer</button></form>';
        if ($page->slug !== '') {
            $menuActions .= '<form method="post" action="/dev/pages/' . $slug . '/set-home" role="none" data-dev-ajax="post-redirect" data-dev-redirect="/dev/pages" data-dev-toast-form="Page définie comme accueil"><button type="submit" role="menuitem"><i class="fa-solid fa-house" aria-hidden="true"></i> Définir comme accueil</button></form>';
        }
        $menuActions .= '<form method="post" action="/dev/pages/' . $slug . '/delete" role="none" data-dev-ajax="post-redirect" data-dev-redirect="/dev/pages" data-dev-toast-form="Page supprimée" data-dev-confirm="Supprimer définitivement la page « ' . htmlspecialchars($page->title, ENT_QUOTES) . ' » ? Cette action est irréversible."><button type="submit" role="menuitem" class="dev-menu__danger"><i class="fa-solid fa-trash" aria-hidden="true"></i> Supprimer</button></form>';

        return '<tr data-page-row data-title="' . htmlspecialchars(mb_strtolower($page->title), ENT_QUOTES)
            . '" data-path="' . htmlspecialchars(mb_strtolower($page->routePath()), ENT_QUOTES) . '">'
            . '<td><a class="dev-table__link" href="/dev/pages/' . $slug . '">'
            . '<span class="dev-table__title">' . htmlspecialchars($page->title, ENT_QUOTES) . '</span>'
            . '<span class="dev-table__path">' . htmlspecialchars($page->routePath(), ENT_QUOTES) . '</span>'
            . '</a></td>'
            . '<td>' . $status . ' ' . $home . '</td>'
            . '<td class="dev-table__muted">' . htmlspecialchars($updated, ENT_QUOTES) . '</td>'
            . '<td class="dev-table__actions"><div class="dev-table__actions-inner">'
            . '<a class="dev-icon-btn" href="/dev/pages/' . $slug . '" title="Éditer" aria-label="Éditer ' . htmlspecialchars($page->title, ENT_QUOTES) . '"><i class="fa-solid fa-pen" aria-hidden="true"></i></a>'
            . '<a class="dev-icon-btn" href="' . htmlspecialchars($page->routePath(), ENT_QUOTES) . '" target="_blank" rel="noopener" title="Voir" aria-label="Voir la page"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a>'
            . '<details class="dev-menu">'
            . '<summary class="dev-icon-btn" aria-label="Plus d\'actions"><i class="fa-solid fa-ellipsis-vertical" aria-hidden="true"></i></summary>'
            . '<div class="dev-menu__panel" role="menu">'
            . $menuActions
            . '</div></details>'
            . '</div></td></tr>';
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
            return $this->respondPageActionError($request, '/dev/pages', 'Le titre est requis.');
        }

        $layout = trim($data['layout'] ?? 'default');
        if (!$this->layouts->exists($layout)) {
            $layout = 'default';
        }

        $slug = PageSlug::normalize($slug);
        $slugError = PageSlug::validate($slug);
        if ($slugError !== null) {
            return $this->respondPageActionError($request, '/dev/pages', $slugError);
        }

        if ($slug !== '' && $this->pages->findBySlug($slug, false) !== null) {
            return $this->respondPageActionError($request, '/dev/pages', 'Cette adresse est déjà utilisée par une autre page.');
        }

        if ($slug === '' && $this->pages->findBySlug('', false) !== null) {
            return $this->respondPageActionError(
                $request,
                '/dev/pages',
                'Une page d\'accueil existe déjà. Définissez une autre page comme accueil avant d\'en créer une nouvelle.',
            );
        }

        $this->pages->save(new Page(
            slug: $slug,
            title: $title,
            layout: $layout,
            description: trim($data['description'] ?? ''),
            sections: PageTemplates::sections(trim($data['page_template'] ?? 'blank')),
            meta: [],
            published: false,
            updatedAt: '',
        ));

        return $this->respondPageAction(
            $request,
            '/dev/pages/' . SlugCodec::encode($slug),
            'Page créée en brouillon',
        );
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
            'view_url' => $page->published
                ? $page->routePath()
                : '/dev/preview/' . SlugCodec::encode($page->slug),
            'view_url_label' => $page->published ? 'Voir' : 'Aperçu',
            'sections_html' => $this->sectionForms->renderAll($page),
            'sections_count' => count($page->sections),
            'block_picker_html' => (new BlockPickerRenderer($this->registry))->renderPickerHtml(),
            'rename_section_html' => $this->buildRenameSectionHtml($page),
            'home_section_html' => $this->buildHomeSectionHtml($page),
            'page_home_menu_html' => $page->slug === ''
                ? ''
                : '<form method="post" action="/dev/pages/' . SlugCodec::encode($page->slug) . '/set-home" role="none" data-dev-ajax="post-redirect" data-dev-redirect="/dev/pages/_" data-dev-toast-form="Page définie comme accueil"><button type="submit" role="menuitem"><i class="fa-solid fa-house" aria-hidden="true"></i> Définir comme accueil</button></form>',
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

        return '<form method="post" action="/dev/pages/' . $slug . '/rename" class="dev-form--grid dev-form--grid-2" data-dev-confirm="Changer l\'adresse de cette page ? Les liens existants vers l\'ancienne adresse ne fonctionneront plus.">'
            . '<div class="dev-field dev-form__full">'
            . '<label class="dev-label" for="new_slug">Nouvelle adresse</label>'
            . '<input class="dev-input" id="new_slug" type="text" name="new_slug" value="' . htmlspecialchars($page->slug, ENT_QUOTES) . '" pattern="[a-z0-9]+(-[a-z0-9]+)*" />'
            . '<span class="dev-hint">Lettres minuscules, chiffres et tirets uniquement. Les liens existants pointant vers l\'ancienne adresse casseront.</span>'
            . '</div>'
            . '<div class="dev-form__full">'
            . '<button type="submit" class="dev-button dev-button--secondary">Mettre à jour l\'adresse</button>'
            . '</div></form>';
    }

    private function buildHomeSectionHtml(Page $page): string
    {
        if ($page->slug === '') {
            return '<p class="dev-hint">Cette page est actuellement la page d\'accueil du site (<code>/</code>).</p>';
        }

        $slug = SlugCodec::encode($page->slug);

        return '<form method="post" action="/dev/pages/' . $slug . '/set-home" data-dev-ajax="post-redirect" data-dev-redirect="/dev/pages/_" data-dev-toast-form="Page définie comme accueil">'
            . '<p class="dev-hint">Remplace la page d\'accueil actuelle. L\'ancienne page d\'accueil prendra l\'adresse <code>/' . htmlspecialchars($page->slug, ENT_QUOTES) . '</code>.</p>'
            . '<button type="submit" class="dev-button dev-button--secondary"><i class="fa-solid fa-house" aria-hidden="true"></i> Définir comme page d\'accueil</button>'
            . '</form>';
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
        $page = $this->pages->findBySlug($decoded, false);
        if ($page === null) {
            return $this->respondPageAction($request, '/dev/pages', 'Page introuvable.');
        }

        if ($decoded === '') {
            return $this->respondPageActionError(
                $request,
                '/dev/pages',
                'Impossible de supprimer la page d\'accueil. Définissez d\'abord une autre page comme accueil.',
            );
        }

        $this->pages->delete($decoded);
        $this->removeNavReferencesToPage($decoded);

        return $this->respondPageAction($request, '/dev/pages', 'Page supprimée.');
    }

    public function setHome(Request $request, string $slug = ''): Response
    {
        $decoded = SlugCodec::decode($slug);
        if ($decoded === '') {
            return $this->respondPageAction($request, '/dev/pages/_', 'Cette page est déjà la page d\'accueil.');
        }

        $page = $this->pages->findBySlug($decoded, false);
        if ($page === null) {
            return $this->respondPageActionError($request, '/dev/pages', 'Page introuvable.');
        }

        $this->swapHomeInNav($decoded);
        $this->pages->setHomePage($decoded);

        return $this->respondPageAction($request, '/dev/pages/_', 'Page définie comme accueil.');
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

        $slugError = PageSlug::validate($newSlug);
        if ($slugError !== null) {
            return $this->ui->withFlash(
                $this->ui->redirect('/dev/pages/' . SlugCodec::encode($page->slug)),
                $slugError,
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

    private function respondPageAction(Request $request, string $redirectTo, string $message): Response
    {
        if ($this->isHx($request)) {
            return $this->ui->withFlash($this->ui->redirect($redirectTo), $message);
        }

        return $this->ui->withFlash($this->ui->redirect($redirectTo), $message . '.');
    }

    private function respondPageActionError(Request $request, string $redirectTo, string $message): Response
    {
        if ($this->isHx($request)) {
            return new Response(422, $message);
        }

        return $this->ui->withFlash($this->ui->redirect($redirectTo), $message);
    }

    private function removeNavReferencesToPage(string $slug): void
    {
        $site = $this->site->getSite();
        $items = is_array($site['nav_items'] ?? null) ? $site['nav_items'] : [];
        if ($items === []) {
            return;
        }

        $filtered = array_values(array_filter(
            $items,
            static fn (array $item): bool => !(
                ($item['type'] ?? '') === 'page'
                && (string) ($item['slug'] ?? '') === $slug
            ),
        ));

        if ($filtered === $items) {
            return;
        }

        $site['nav_items'] = $filtered;
        $this->site->setSite($site);
    }

    private function swapHomeInNav(string $newHomeSlug): void
    {
        $site = $this->site->getSite();
        $items = is_array($site['nav_items'] ?? null) ? $site['nav_items'] : [];
        if ($items === []) {
            return;
        }

        $changed = false;
        foreach ($items as $i => $item) {
            if (($item['type'] ?? '') !== 'page') {
                continue;
            }
            $itemSlug = (string) ($item['slug'] ?? '');
            if ($itemSlug === $newHomeSlug) {
                $items[$i]['slug'] = '';
                $changed = true;
            } elseif ($itemSlug === '') {
                $items[$i]['slug'] = $newHomeSlug;
                $changed = true;
            }
        }

        if ($changed) {
            $site['nav_items'] = $items;
            $this->site->setSite($site);
        }
    }
}
