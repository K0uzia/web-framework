<?php

declare(strict_types=1);

namespace App\Http\Admin;

use Capsule\AdminDashboard;
use Capsule\ClientDashboardConfig;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\Http\Support\FormData;
use Capsule\Page;
use Capsule\PageRepository;
use Capsule\SiteRepository;

final class PagesController
{
    public function __construct(
        private readonly AdminDashboard $ui,
        private readonly SiteRepository $site,
        private readonly PageRepository $pages,
        private readonly PageEditFormRenderer $formRenderer,
        private readonly PageEditContentApplier $contentApplier,
    ) {
    }

    public function index(Request $request): Response
    {
        $config = $this->site->getClientDashboard();
        $pagesBySlug = [];
        foreach ($this->pages->all() as $page) {
            $pagesBySlug[$page->slug] = $page;
        }

        $cards = [];
        foreach (array_keys($config['pages']) as $slug) {
            if (!is_string($slug)) {
                continue;
            }
            $sections = $config['pages'][$slug]['sections'] ?? [];
            if (!is_array($sections) || $sections === []) {
                continue;
            }
            $fieldCount = 0;
            foreach ($sections as $section) {
                if (is_array($section) && is_array($section['fields'] ?? null)) {
                    $fieldCount += count($section['fields']);
                }
            }
            $page = $pagesBySlug[$slug] ?? null;
            $title = $page instanceof Page ? $page->title : ($slug === '' ? 'Accueil' : $slug);
            $path = $slug === '' ? '/' : '/' . $slug;
            $cards[] = [
                'slug' => $slug,
                'title' => $title,
                'path' => $path,
                'sections' => count($sections),
                'fields' => $fieldCount,
            ];
        }

        usort($cards, static fn (array $a, array $b): int => strcmp((string) $a['path'], (string) $b['path']));

        return $this->ui->render('pages-index.html', [
            'title' => 'Pages',
            'nav_section' => 'pages',
            'flash' => $this->ui->flashFromRequest($request),
            'pages_list_html' => $this->renderList($cards),
            'empty_class' => $cards === [] ? '' : 'hidden',
            'list_class' => $cards === [] ? 'hidden' : '',
        ]);
    }

    public function edit(Request $request, string $slug): Response
    {
        $slug = $slug === '_' ? '' : $slug;
        $config = $this->site->getClientDashboard();
        if (!ClientDashboardConfig::isPageEditable($config, $slug)) {
            return $this->ui->withFlash(
                $this->ui->redirect('/admin/pages'),
                'Cette page n\'est pas ouverte à l\'édition.',
            );
        }

        $page = $this->pages->findBySlug($slug, false);
        if ($page === null) {
            return $this->ui->withFlash(
                $this->ui->redirect('/admin/pages'),
                'Page introuvable.',
            );
        }

        $path = $slug === '' ? '/' : '/' . $slug;
        $sections = $config['pages'][$slug]['sections'] ?? [];
        $fieldCount = 0;
        if (is_array($sections)) {
            foreach ($sections as $section) {
                if (is_array($section) && is_array($section['fields'] ?? null)) {
                    $fieldCount += count($section['fields']);
                }
            }
        }

        $slugEnc = rawurlencode($slug === '' ? '_' : $slug);
        $mediasHint = ClientDashboardConfig::isMediasEnabled($config)
            ? '<p class="admin-hint">Images et vidéos : <a href="/admin/medias">bibliothèque médias</a>.</p>'
            : '';

        return $this->ui->render('page-edit.html', [
            'title' => 'Modifier : ' . $page->title,
            'nav_section' => 'pages',
            'flash' => $this->ui->flashFromRequest($request),
            'page_title' => $page->title,
            'page_path' => $path,
            'form_action' => '/admin/pages/' . $slugEnc,
            'fields_count' => (string) $fieldCount,
            'sections_count' => (string) (is_array($sections) ? count($sections) : 0),
            'form_fields_html' => $this->formRenderer->render($page, $config),
            'medias_hint_html' => $mediasHint,
        ]);
    }

    public function update(Request $request, string $slug): Response
    {
        $slug = $slug === '_' ? '' : $slug;
        $config = $this->site->getClientDashboard();
        if (!ClientDashboardConfig::isPageEditable($config, $slug)) {
            return $this->ui->withFlash(
                $this->ui->redirect('/admin/pages'),
                'Cette page n\'est pas ouverte à l\'édition.',
            );
        }

        $page = $this->pages->findBySlug($slug, false);
        if ($page === null) {
            return $this->ui->withFlash(
                $this->ui->redirect('/admin/pages'),
                'Page introuvable.',
            );
        }

        $slugEnc = rawurlencode($slug === '' ? '_' : $slug);
        $sections = $this->contentApplier->apply($page, $config, FormData::fromRequest($request));
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

        return $this->ui->withFlash(
            $this->ui->redirect('/admin/pages/' . $slugEnc),
            'Contenu enregistré.',
        );
    }

    /**
     * @param list<array{slug: string, title: string, path: string, sections: int, fields: int}> $cards
     */
    private function renderList(array $cards): string
    {
        if ($cards === []) {
            return '';
        }

        $rows = [];
        foreach ($cards as $card) {
            $title = htmlspecialchars($card['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $path = htmlspecialchars($card['path'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $slugEnc = rawurlencode($card['slug'] === '' ? '_' : $card['slug']);
            $meta = $card['sections'] . ' section' . ($card['sections'] > 1 ? 's' : '')
                . ' · ' . $card['fields'] . ' champ' . ($card['fields'] > 1 ? 's' : '');

            $rows[] = '<tr>'
                . '<td><span class="admin-table__title">' . $title . '</span>'
                . '<span class="admin-table__meta">' . $path . '</span></td>'
                . '<td class="admin-table__muted">' . htmlspecialchars($meta, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</td>'
                . '<td class="admin-table__actions">'
                . '<a class="admin-button admin-button--primary admin-button--sm" href="/admin/pages/' . $slugEnc . '">'
                . '<i class="fa-solid fa-pen" aria-hidden="true"></i> Modifier</a>'
                . '</td>'
                . '</tr>';
        }

        return '<div class="admin-table-wrap"><table class="admin-table">'
            . '<thead><tr><th>Page</th><th>Autorisations</th><th><span class="visually-hidden">Actions</span></th></tr></thead>'
            . '<tbody>' . implode('', $rows) . '</tbody></table></div>';
    }
}
