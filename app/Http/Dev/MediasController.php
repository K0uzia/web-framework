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
        private readonly LibraryMediaUploader $libraryUploader,
        private readonly MediaUploader $siteUploader,
        private readonly MediaUsageScanner $usage,
    ) {
    }

    public function index(Request $request): Response
    {
        $tab = ($request->query['tab'] ?? 'images') === 'videos' ? 'videos' : 'images';
        $this->library->syncDiscoveredRecords('image');
        $this->library->syncDiscoveredRecords('video');
        $importedImages = $this->media->all('image', MediaRepository::OWNER_DEV);
        $videos = $this->media->all('video', MediaRepository::OWNER_DEV);

        return $this->ui->render('medias-index.html', [
            'title' => 'Médias',
            'crumb_html' => Breadcrumb::render([['label' => 'Médias']]),
            'tab' => $tab,
            'tab_images_class' => $tab === 'images' ? 'is-active' : '',
            'tab_videos_class' => $tab === 'videos' ? 'is-active' : '',
            'tab_images_selected' => $tab === 'images' ? 'true' : 'false',
            'tab_videos_selected' => $tab === 'videos' ? 'true' : 'false',
            'images_count' => (string) count($importedImages),
            'videos_count' => (string) count($videos),
            'images_grid_html' => $this->renderImagesPanel($importedImages),
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
                ? $this->libraryUploader->storeVideo($file)
                : $this->libraryUploader->storeImage($file);
            if ($this->media->findByUrl($url) === null) {
                $meta = $this->libraryUploader->storedFileMeta(
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
                    MediaRepository::OWNER_DEV,
                );
            }
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

        if ($this->library->isBundledAsset($record['url'])) {
            return $this->respondGrid($request, $record['kind'] === 'video' ? 'videos' : 'images', 'Les visuels modèles intégrés ne peuvent pas être supprimés.');
        }

        if (($record['owner'] ?? '') === MediaRepository::OWNER_CLIENT) {
            return $this->respondGrid(
                $request,
                $record['kind'] === 'video' ? 'videos' : 'images',
                'Ce média appartient à la galerie client.',
            );
        }

        $usages = $this->usage->usages($record['url']);
        if ($usages !== []) {
            return $this->respondGrid($request, $record['kind'] === 'video' ? 'videos' : 'images', 'Ce média est encore utilisé sur le site.');
        }

        $this->deleteStoredFile($record['url']);
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
        $this->library->syncDiscoveredRecords($tab === 'videos' ? 'video' : 'image');
        $html = $tab === 'videos'
            ? $this->renderGrid($this->media->all('video', MediaRepository::OWNER_DEV))
            : $this->renderImagesPanel($this->media->all('image', MediaRepository::OWNER_DEV));

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
     * @param list<array{id: string, kind: string, url: string, filename: string, mime: string, size: int, label: string, created_at: string}> $imported
     */
    private function renderImagesPanel(array $imported): string
    {
        $html = '<div class="dev-medias-section"><h3 class="dev-medias-section__title">Vos images</h3>';
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
            $isBundled = $this->library->isBundledAsset($record['url']);
            $isReadonly = $readonly || (bool) ($record['readonly'] ?? false) || $isBundled;
            $safeId = htmlspecialchars($record['id'], ENT_QUOTES);
            $safeUrl = htmlspecialchars($record['url'], ENT_QUOTES);
            $usage = $this->usage->report($record['url']);
            $inUse = $usage['total_places'] > 0;

            if ($record['kind'] === 'video') {
                $visual = '<video class="dev-media-card__video" src="' . $safeUrl . '" muted playsinline preload="metadata"></video>';
            } else {
                $visual = '<img class="dev-media-card__img" src="' . $safeUrl . '" alt="" loading="lazy" decoding="async" />';
            }

            if ($isBundled) {
                $visual .= '<span class="dev-media-card__overlay-badge"><i class="fa-solid fa-layer-group" aria-hidden="true"></i> Modèle</span>';
            }

            $deleteBtn = $this->renderDeleteAction($safeId, $isReadonly, $inUse, $usage);

            $label = trim((string) ($record['label'] ?? ''));
            if ($label === '' || str_starts_with($label, 'Modèle :')) {
                $label = basename((string) $record['filename']);
            }

            $cards[] = '<article class="dev-media-card' . ($isReadonly ? ' dev-media-card--readonly' : '') . ($inUse ? ' dev-media-card--in-use' : '') . '">'
                . '<div class="dev-media-card__visual">' . $visual . '</div>'
                . '<div class="dev-media-card__meta">'
                . '<p class="dev-media-card__name">' . htmlspecialchars($label, ENT_QUOTES) . '</p>'
                . $this->renderUsageHtml($usage)
                . '</div>'
                . '<div class="dev-media-card__actions">' . $deleteBtn . '</div>'
                . '</article>';
        }

        return '<div class="dev-media-grid">' . implode('', $cards) . '</div>';
    }

    /**
     * @param array{total_places: int, page_count: int, block_count: int, site_labels: list<string>, entries: list<array{kind: string, page: string, page_path: string, section_type: string, section_id: string, detail: string}>} $usage
     */
    private function renderUsageHtml(array $usage): string
    {
        if ($usage['total_places'] === 0) {
            return '<p class="dev-media-card__usage dev-media-card__usage--none"><i class="fa-solid fa-circle" aria-hidden="true"></i> Non utilisé</p>';
        }

        $parts = [];
        if ($usage['site_labels'] !== []) {
            $parts[] = 'identité du site';
        }
        if ($usage['page_count'] > 0) {
            $parts[] = $usage['page_count'] . ' page' . ($usage['page_count'] > 1 ? 's' : '');
        }
        if ($usage['block_count'] > 0) {
            $parts[] = $usage['block_count'] . ' bloc' . ($usage['block_count'] > 1 ? 's' : '');
        }

        $html = '<p class="dev-media-card__usage dev-media-card__usage--active"><i class="fa-solid fa-link" aria-hidden="true"></i> '
            . htmlspecialchars(implode(' · ', $parts), ENT_QUOTES) . '</p>';

        $details = $usage['site_labels'];
        foreach ($usage['entries'] as $entry) {
            $details[] = $entry['detail'];
        }
        $details = array_values(array_unique($details));
        if ($details !== []) {
            $html .= '<ul class="dev-media-card__usage-list">';
            foreach (array_slice($details, 0, 4) as $detail) {
                $html .= '<li>' . htmlspecialchars($detail, ENT_QUOTES) . '</li>';
            }
            if (count($details) > 4) {
                $html .= '<li class="dev-media-card__usage-more">+' . (count($details) - 4) . ' autre(s)</li>';
            }
            $html .= '</ul>';
        }

        return $html;
    }

    /**
     * @param array{total_places: int, page_count: int, block_count: int, site_labels: list<string>, entries: list<array{kind: string, page: string, page_path: string, section_type: string, section_id: string, detail: string}>} $usage
     */
    private function renderDeleteAction(string $safeId, bool $isReadonly, bool $inUse, array $usage): string
    {
        if ($isReadonly) {
            return '<span class="dev-media-card__action-hint" title="Visuel modèle intégré au framework"><i class="fa-solid fa-lock" aria-hidden="true"></i> Non supprimable</span>';
        }

        if ($inUse) {
            $title = 'Utilisé sur le site. Retirez-le des blocs avant suppression.';
            if ($usage['entries'] !== []) {
                $title = 'Utilisé : ' . implode(', ', array_slice(array_column($usage['entries'], 'detail'), 0, 2));
            }

            return '<button type="button" class="dev-button dev-button--ghost dev-button--sm dev-media-card__delete" disabled title="' . htmlspecialchars($title, ENT_QUOTES) . '" aria-label="Suppression impossible, média encore utilisé">'
                . '<i class="fa-solid fa-trash" aria-hidden="true"></i> Supprimer</button>';
        }

        return '<form class="dev-inline-form dev-media-card__delete-form" method="post" action="/dev/medias/' . $safeId . '/delete" data-dev-ajax="medias-grid" data-dev-toast-form="Média supprimé">'
            . '<button type="submit" class="dev-button dev-button--ghost dev-button--sm dev-media-card__delete dev-media-card__delete--danger" aria-label="Supprimer ce média" title="Supprimer">'
            . '<i class="fa-solid fa-trash" aria-hidden="true"></i> Supprimer</button></form>';
    }

    private function deleteStoredFile(string $url): void
    {
        if ($this->libraryUploader->isManagedUrl($url)) {
            $this->deleteManagedUpload($this->libraryUploader, $url);

            return;
        }

        if ($this->siteUploader->isManagedUrl($url)) {
            $this->siteUploader->delete($url);

            return;
        }

        $path = $this->library->publicUrlToPath($url);
        if ($path !== null && is_file($path)) {
            @unlink($path);
        }
    }

    private function deleteManagedUpload(MediaUploader $uploader, string $url): void
    {
        $path = $this->library->publicUrlToPath($url);
        if ($path !== null && is_file($path)) {
            @unlink($path);

            return;
        }

        $uploader->delete($url);
    }
}
