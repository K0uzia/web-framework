<?php

declare(strict_types=1);

namespace App\Http\Dev;

use Capsule\DevDashboard;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\PageRepository;
use Capsule\SiteRepository;

final class OverviewController
{
    public function __construct(
        private readonly DevDashboard $ui,
        private readonly PageRepository $pages,
        private readonly SiteRepository $site,
    ) {
    }

    public function index(Request $request): Response
    {
        $pages = $this->pages->all();
        $total = count($pages);
        $published = count(array_filter($pages, static fn ($p) => $p->published));
        $drafts = $total - $published;

        $site = $this->site->getSite();
        $theme = $this->site->getTheme();
        $colors = is_array($theme['colors'] ?? null) ? $theme['colors'] : [];
        $navItems = is_array($site['nav_items'] ?? null) ? $site['nav_items'] : [];
        $navCount = ($site['nav_mode'] ?? 'auto') === 'custom' ? count($navItems) : $published;

        usort($pages, static fn ($a, $b) => strcmp((string) $b->updatedAt, (string) $a->updatedAt));
        $recent = array_slice($pages, 0, 5);

        $recentHtml = $recent === []
            ? '<p class="dev-empty">Aucune page pour le moment.</p>'
            : '<div class="dev-list">' . implode('', array_map(
                fn ($p) => $this->recentRow($p),
                $recent,
            )) . '</div>';

        return $this->ui->render('overview.html', [
            'title' => 'Tableau de bord',
            'crumb_html' => Breadcrumb::render([['label' => 'Tableau de bord']]),
            'site_name' => (string) ($site['name'] ?? 'CapsulePHP'),
            'stat_pages_total' => (string) $total,
            'stat_pages_published' => (string) $published,
            'stat_pages_drafts' => (string) $drafts,
            'stat_nav_count' => (string) $navCount,
            'theme_primary' => (string) ($colors['primary'] ?? '#3b82f6'),
            'theme_secondary' => (string) ($colors['secondary'] ?? '#64748b'),
            'theme_background' => (string) ($colors['background'] ?? '#ffffff'),
            'recent_pages_html' => $recentHtml,
            'flash' => $this->ui->flashFromRequest($request),
        ], section: 'overview');
    }

    private function recentRow(\Capsule\Page $page): string
    {
        $slug = SlugCodec::encode($page->slug);
        $status = $page->published
            ? '<span class="dev-badge dev-badge--success">Publiée</span>'
            : '<span class="dev-badge dev-badge--muted">Brouillon</span>';

        return '<a class="dev-list__row" href="/dev/pages/' . $slug . '">'
            . '<span class="dev-list__icon" aria-hidden="true"><i class="fa-solid fa-file-lines"></i></span>'
            . '<span class="dev-list__main">'
            . '<span class="dev-list__title">' . htmlspecialchars($page->title, ENT_QUOTES) . '</span>'
            . '<span class="dev-list__meta">' . htmlspecialchars($page->routePath(), ENT_QUOTES) . '</span>'
            . '</span>'
            . $status
            . '<span class="dev-list__arrow" aria-hidden="true"><i class="fa-solid fa-chevron-right"></i></span>'
            . '</a>';
    }
}
