<?php

declare(strict_types=1);

namespace App\Http\Dev;

use Capsule\DevDashboard;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\MediaLibrary;
use Capsule\MediaRepository;
use Capsule\MediaUsageScanner;

final class MediasController
{
    use DevHx;

    public function __construct(
        private readonly DevDashboard $ui,
        private readonly MediaRepository $media,
        private readonly MediaLibrary $library,
        private readonly LibraryMediaUploader $uploader,
        private readonly MediaUsageScanner $usage,
    ) {
    }

    public function index(Request $request): Response
    {
        $tab = ($request->query['tab'] ?? 'images') === 'videos' ? 'videos' : 'images';
        $stockImages = $this->library->stockImageRecords();
        $importedImages = $this->media->all('image');
        $videos = $this->media->all('video');

        return $this->ui->render('medias-index.html', [
            'title' => 'Médias',
            'crumb_html' => Breadcrumb::render([['label' => 'Médias']]),
            'tab' => $tab,
            'tab_images_class' => $tab === 'images' ? 'is-active' : '',
            'tab_videos_class' => $tab === 'videos' ? 'is-active' : '',
            'tab_images_selected' => $tab === 'images' ? 'true' : 'false',
            'tab_videos_selected' => $tab === 'videos' ? 'true' : 'false',
            'images_count' => (string) (count($stockImages) + count($importedImages)),
            'videos_count' => (string) count($videos),
            'images_grid_html' => $this->renderImagesPanel($stockImages, $importedImages),
            'videos_grid_html' => $this->renderGrid($videos),
            'flash' => $this->ui->flashFromRequest($request),
        ], section: 'medias');
    }

    public function upload(Request $request): Response
    {
        $data = $request->query;
        $kind = ($data['kind'] ?? 'image') === 'video' ? 'video' : 'image';
        $error = '';
        $file = $request->files['file'] ?? null;

        try {
            if (!is_array($file)) {
                throw new MediaUploadException('Aucun fichier reçu.');
            }
            $url = $kind === 'video'
                ? $this->uploader->storeVideo($file)
                : $this->uploader->storeImage($file);
            $this->media->create(
                $kind,
                $url,
                basename($url),
                (string) ($file['type'] ?? ''),
                (int) ($file['size'] ?? 0),
            );
        } catch (MediaUploadException $e) {
            $error = $e->getMessage();
        }

        return $this->respondUpload($request, $kind, $error);
    }

    public function destroy(Request $request, string $id): Response
    {
        $record = $this->media->findById($id);
        if ($record === null) {
            return $this->respondGrid($request, 'images', 'Média introuvable.');
        }

        $usages = $this->usage->usages($record['url']);
        if ($usages !== []) {
            return $this->respondGrid($request, $record['kind'] === 'video' ? 'videos' : 'images', 'Ce média est encore utilisé sur le site.');
        }

        $this->uploader->delete($record['url']);
        $this->media->delete($id);

        return $this->respondGrid($request, $record['kind'] === 'video' ? 'videos' : 'images', 'Média supprimé.');
    }

    private function respondUpload(Request $request, string $kind, string $error): Response
    {
        $tab = $kind === 'video' ? 'videos' : 'images';
        if ($this->isHx($request)) {
            return $this->respondGrid($request, $tab, $error !== '' ? $error : 'Fichier importé.');
        }

        $redirect = '/dev/medias?tab=' . $tab;
        if ($error !== '') {
            return $this->ui->withFlash($this->ui->redirect($redirect), $error);
        }

        return $this->ui->withFlash($this->ui->redirect($redirect), 'Fichier importé.');
    }

    private function respondGrid(Request $request, string $tab, string $message): Response
    {
        $html = $tab === 'videos'
            ? $this->renderGrid($this->media->all('video'))
            : $this->renderImagesPanel($this->library->stockImageRecords(), $this->media->all('image'));

        if ($this->isHx($request)) {
            $kind = $tab === 'videos' ? 'video' : 'image';
            $payload = '<div id="dev-medias-grid-' . $kind . '" data-dev-medias-grid>' . $html . '</div>';
            if ($message !== '') {
                $payload .= '<p class="dev-hint" data-dev-medias-status hidden>' . htmlspecialchars($message, ENT_QUOTES) . '</p>';
            }

            return $this->ui->fragment($payload);
        }

        return $this->ui->withFlash($this->ui->redirect('/dev/medias?tab=' . $tab), $message);
    }

    /**
     * @param list<array{id: string, kind: string, url: string, filename: string, mime: string, size: int, label: string, created_at: string, readonly?: bool}> $stock
     * @param list<array{id: string, kind: string, url: string, filename: string, mime: string, size: int, label: string, created_at: string}> $imported
     */
    private function renderImagesPanel(array $stock, array $imported): string
    {
        $html = '';
        if ($stock !== []) {
            $html .= '<div class="dev-medias-section"><h3 class="dev-medias-section__title">Images d\'exemple</h3>'
                . '<p class="dev-hint">Visuels fournis avec le thème. Utilisables dans les blocs, non supprimables.</p>'
                . $this->renderGrid($stock, readonly: true)
                . '</div>';
        }

        $html .= '<div class="dev-medias-section"><h3 class="dev-medias-section__title">Vos images</h3>';
        if ($imported === []) {
            $html .= '<p class="dev-empty"><i class="fa-solid fa-photo-film" aria-hidden="true"></i>Aucun fichier importé.</p>';
        } else {
            $html .= $this->renderGrid($imported);
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * @param list<array{id: string, kind: string, url: string, filename: string, mime: string, size: int, label: string, created_at: string, readonly?: bool}> $records
     */
    private function renderGrid(array $records, bool $readonly = false): string
    {
        if ($records === []) {
            return '';
        }

        $cards = [];
        foreach ($records as $record) {
            $isReadonly = $readonly || (bool) ($record['readonly'] ?? false);
            $safeId = htmlspecialchars($record['id'], ENT_QUOTES);
            $safeUrl = htmlspecialchars($record['url'], ENT_QUOTES);
            $usages = $this->usage->usages($record['url']);
            $inUse = $usages !== [];
            $usageHtml = $inUse
                ? '<p class="dev-media-card__usage">Utilisé : ' . htmlspecialchars(implode(', ', array_slice($usages, 0, 2)), ENT_QUOTES) . '</p>'
                : '';

            if ($record['kind'] === 'video') {
                $visual = '<video class="dev-media-card__video" src="' . $safeUrl . '" muted playsinline preload="metadata"></video>';
            } else {
                $visual = '<img class="dev-media-card__img" src="' . $safeUrl . '" alt="" loading="lazy" decoding="async" />';
            }

            if ($isReadonly) {
                $deleteBtn = '<span class="dev-media-card__badge"><i class="fa-solid fa-lock" aria-hidden="true"></i> Exemple</span>';
            } elseif ($inUse) {
                $deleteBtn = '<button type="button" class="dev-icon-btn" disabled title="Média encore utilisé"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>';
            } else {
                $deleteBtn = '<form class="dev-inline-form" method="post" action="/dev/medias/' . $safeId . '/delete" data-dev-ajax="medias-grid" data-dev-toast-form="Média supprimé">'
                    . '<button type="submit" class="dev-icon-btn dev-icon-btn--danger" aria-label="Supprimer" title="Supprimer"><i class="fa-solid fa-trash" aria-hidden="true"></i></button></form>';
            }

            $label = $isReadonly ? ($record['label'] ?? 'Image d\'exemple') : $record['filename'];

            $cards[] = '<article class="dev-media-card' . ($isReadonly ? ' dev-media-card--readonly' : '') . '">'
                . '<div class="dev-media-card__visual">' . $visual . '</div>'
                . '<div class="dev-media-card__meta">'
                . '<p class="dev-media-card__name">' . htmlspecialchars((string) $label, ENT_QUOTES) . '</p>'
                . '<p class="dev-media-card__url"><code>' . $safeUrl . '</code></p>'
                . $usageHtml
                . '</div>'
                . '<div class="dev-media-card__actions">' . $deleteBtn . '</div>'
                . '</article>';
        }

        return '<div class="dev-media-grid">' . implode('', $cards) . '</div>';
    }
}
