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
        private readonly PagesListRenderer $listRenderer = new PagesListRenderer(),
    ) {
    }

    public function index(Request $request): Response
    {
        $rows = PageRowsBuilder::build($this->site, $this->pages);

        return $this->ui->render('pages-index.html', [
            'title' => 'Pages',
            'nav_section' => 'pages',
            'main_class' => '',
            'flash' => $this->ui->flashFromRequest($request),
            'pages_count' => (string) count($rows),
            'rows_html' => $this->listRenderer->renderRows($rows),
            'table_class' => $rows === [] ? 'hidden' : '',
            'empty_class' => $rows === [] ? '' : 'hidden',
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
        $sidebar = PageEditorContext::sidebar($page);
        $blockCount = 0;
        if (is_array($sections)) {
            $blockCount = count($sections);
        }

        return $this->ui->render('page-edit.html', [
            'title' => 'Modifier : ' . $page->title,
            'nav_section' => 'pages',
            'main_class' => 'admin-main--document',
            'flash' => $this->ui->flashFromRequest($request),
            'page_title' => $page->title,
            'page_path' => $path,
            'form_action' => '/admin/pages/' . $slugEnc,
            'fields_count' => (string) $fieldCount,
            'sections_count' => (string) $blockCount,
            'form_fields_html' => $this->formRenderer->render($page, $config),
            'page_updated_iso' => $page->updatedAt,
            'save_status_saved' => 'is-visible',
            'save_status_dirty' => '',
            ...$sidebar,
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
}
