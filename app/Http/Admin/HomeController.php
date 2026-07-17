<?php

declare(strict_types=1);

namespace App\Http\Admin;

use Capsule\AdminDashboard;
use Capsule\ClientDashboardConfig;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\MediaRepository;
use Capsule\PageRepository;
use Capsule\SiteRepository;

final class HomeController
{
    public function __construct(
        private readonly AdminDashboard $ui,
        private readonly SiteRepository $site,
        private readonly PageRepository $pages,
        private readonly MediaRepository $media,
        private readonly PagesListRenderer $listRenderer = new PagesListRenderer(),
    ) {
    }

    public function index(Request $request): Response
    {
        $rows = PageRowsBuilder::build($this->site, $this->pages);
        $config = $this->site->getClientDashboard();
        $mediasEnabled = ClientDashboardConfig::isMediasEnabled($config);
        $siteEnabled = ClientDashboardConfig::isSiteEnabled($config);
        $recent = PageRowsBuilder::mostRecent($rows, 4);
        $stats = new DashboardStats($this->site, $this->pages, $this->media);
        $cards = $stats->build($rows);

        return $this->ui->render('dashboard.html', [
            'title' => 'Tableau de bord',
            'nav_section' => 'home',
            'flash' => $this->ui->flashFromRequest($request),
            'stats_html' => $stats->renderCards($cards),
            'medias_hidden' => $mediasEnabled ? '' : 'hidden',
            'site_hidden' => $siteEnabled ? '' : 'hidden',
            'empty_class' => $rows === [] ? '' : 'hidden',
            'recent_class' => $rows === [] ? 'hidden' : '',
            'recent_rows_html' => $this->listRenderer->renderRows($recent),
        ]);
    }
}
