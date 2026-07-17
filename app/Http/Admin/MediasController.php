<?php

declare(strict_types=1);

namespace App\Http\Admin;

use App\Http\Dev\ClientMediaUploader;
use App\Http\Dev\MediaUploadException;
use Capsule\AdminDashboard;
use Capsule\ClientDashboardConfig;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\MediaLibrary;
use Capsule\MediaRepository;
use Capsule\MediaUsageScanner;
use Capsule\SiteRepository;

final class MediasController
{
    public function __construct(
        private readonly AdminDashboard $ui,
        private readonly SiteRepository $site,
        private readonly MediaRepository $media,
        private readonly MediaLibrary $library,
        private readonly ClientMediaUploader $clientUploader,
        private readonly MediaUsageScanner $usage,
        private readonly string $publicRoot,
    ) {
    }

    public function index(Request $request): Response
    {
        $denied = $this->guardMedias();
        if ($denied !== null) {
            return $denied;
        }

        $tab = ($request->query['tab'] ?? 'images') === 'videos' ? 'videos' : 'images';
        $this->library->syncClientRecords('image');
        $this->library->syncClientRecords('video');
        $images = $this->media->all('image', MediaRepository::OWNER_CLIENT);
        $videos = $this->media->all('video', MediaRepository::OWNER_CLIENT);

        return $this->ui->render('medias-index.html', [
            'title' => 'Médias',
            'nav_section' => 'medias',
            'flash' => $this->ui->flashFromRequest($request),
            'tab_images_class' => $tab === 'images' ? 'is-active' : '',
            'tab_videos_class' => $tab === 'videos' ? 'is-active' : '',
            'tab_images_selected' => $tab === 'images' ? 'true' : 'false',
            'tab_videos_selected' => $tab === 'videos' ? 'true' : 'false',
            'panel_images_class' => $tab === 'images' ? 'is-active' : '',
            'panel_videos_class' => $tab === 'videos' ? 'is-active' : '',
            'images_count' => (string) count($images),
            'videos_count' => (string) count($videos),
            'images_grid_html' => $this->renderGrid($images),
            'videos_grid_html' => $this->renderGrid($videos),
        ]);
    }

    public function upload(Request $request): Response
    {
        $denied = $this->guardMedias();
        if ($denied !== null) {
            return $denied;
        }

        $kind = (($request->query['kind'] ?? 'image') === 'video') ? 'video' : 'image';
        $tab = $kind === 'video' ? 'videos' : 'images';
        $file = $request->files['file'] ?? null;

        try {
            if (!is_array($file)) {
                throw new MediaUploadException('Aucun fichier reçu.');
            }
            $url = $kind === 'video'
                ? $this->clientUploader->storeVideo($file)
                : $this->clientUploader->storeImage($file);
            if ($this->media->findByUrl($url) === null) {
                $meta = $this->clientUploader->storedFileMeta(
                    $url,
                    (string) ($file['type'] ?? ''),
                    (int) ($file['size'] ?? 0),
                );
                $this->media->create(
                    $kind,
                    $url,
                    basename($url),
                    $meta['mime'],
                    $meta['size'],
                    '',
                    MediaRepository::OWNER_CLIENT,
                );
            }

            return $this->ui->withFlash($this->ui->redirect('/admin/medias?tab=' . $tab), 'Fichier importé.');
        } catch (MediaUploadException $e) {
            return $this->ui->withFlash($this->ui->redirect('/admin/medias?tab=' . $tab), $e->getMessage());
        }
    }

    public function destroy(Request $request, string $id): Response
    {
        $denied = $this->guardMedias();
        if ($denied !== null) {
            return $denied;
        }

        $record = $this->media->findById($id);
        $tab = 'images';
        if ($record === null) {
            return $this->ui->withFlash($this->ui->redirect('/admin/medias'), 'Média introuvable.');
        }
        $tab = $record['kind'] === 'video' ? 'videos' : 'images';

        if (($record['owner'] ?? '') !== MediaRepository::OWNER_CLIENT) {
            return $this->ui->withFlash(
                $this->ui->redirect('/admin/medias?tab=' . $tab),
                'Ce média n\'appartient pas à votre galerie.',
            );
        }
        if ($this->library->isBundledAsset($record['url'])) {
            return $this->ui->withFlash(
                $this->ui->redirect('/admin/medias?tab=' . $tab),
                'Ce fichier modèle ne peut pas être supprimé.',
            );
        }
        if ($this->usage->usages($record['url']) !== []) {
            return $this->ui->withFlash(
                $this->ui->redirect('/admin/medias?tab=' . $tab),
                'Ce média est encore utilisé sur le site.',
            );
        }

        $this->deleteStoredFile($record['url']);
        $this->media->delete($id);

        return $this->ui->withFlash($this->ui->redirect('/admin/medias?tab=' . $tab), 'Média supprimé.');
    }

    private function guardMedias(): ?Response
    {
        if (ClientDashboardConfig::isMediasEnabled($this->site->getClientDashboard())) {
            return null;
        }

        return $this->ui->withFlash(
            $this->ui->redirect('/admin/pages'),
            'La médiathèque n\'est pas autorisée pour votre compte.',
        );
    }

    /**
     * @param list<array{id: string, kind: string, url: string, filename: string}> $records
     */
    private function renderGrid(array $records): string
    {
        if ($records === []) {
            return '<div class="admin-empty admin-empty--compact">'
                . '<p class="admin-empty__title">Aucun fichier</p>'
                . '<p class="admin-empty__text">Importez un fichier pour l\'utiliser dans vos pages.</p>'
                . '</div>';
        }

        $cards = [];
        foreach ($records as $record) {
            $safeId = htmlspecialchars($record['id'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeUrl = htmlspecialchars($record['url'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $name = htmlspecialchars($record['filename'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $visual = $record['kind'] === 'video'
                ? '<video class="admin-media-card__media" src="' . $safeUrl . '" muted playsinline preload="metadata"></video>'
                : '<img class="admin-media-card__media" src="' . $safeUrl . '" alt="" width="160" height="120" loading="lazy" />';

            $cards[] = '<article class="admin-media-card">'
                . '<div class="admin-media-card__preview">' . $visual . '</div>'
                . '<div class="admin-media-card__body">'
                . '<p class="admin-media-card__name">' . $name . '</p>'
                . '<form method="post" action="/admin/medias/' . $safeId . '/delete" class="admin-media-card__delete" data-admin-confirm="Supprimer ce média ?">'
                . '<button type="submit" class="admin-btn admin-btn--ghost admin-btn--sm">'
                . '<i class="fa-solid fa-trash" aria-hidden="true"></i> Supprimer</button>'
                . '</form></div></article>';
        }

        return '<div class="admin-media-grid">' . implode('', $cards) . '</div>';
    }

    private function deleteStoredFile(string $url): void
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            return;
        }
        $full = $this->publicRoot . $path;
        if (is_file($full)) {
            @unlink($full);
        }
    }
}
