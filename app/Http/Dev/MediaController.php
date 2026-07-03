<?php

declare(strict_types=1);

namespace App\Http\Dev;

use Capsule\DevDashboard;
use Capsule\Http\Factory\ResponseFactory;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\SiteRepository;

final class MediaController
{
    use DevHx;

    private const FIELDS = ['logo', 'favicon', 'og_image'];

    public function __construct(
        private readonly DevDashboard $ui,
        private readonly SiteRepository $site,
        private readonly MediaUploader $uploader,
        private readonly ResponseFactory $responses,
    ) {
    }

    public function upload(Request $request, string $field): Response
    {
        if (!in_array($field, self::FIELDS, true)) {
            return $this->ui->redirect('/dev/site');
        }

        $error = '';
        $file = $request->files['file'] ?? null;

        try {
            if (!is_array($file)) {
                throw new MediaUploadException('Aucun fichier reçu.');
            }
            $url = $this->uploader->store($field, $file);

            $site = $this->site->getSite();
            $previousUrl = (string) ($site[$field . '_url'] ?? '');
            if ($previousUrl !== '' && $previousUrl !== $url) {
                $this->uploader->delete($previousUrl);
            }
            $site[$field . '_url'] = $url;
            $this->site->setSite($site);
        } catch (MediaUploadException $e) {
            $error = $e->getMessage();
        }

        return $this->respond($request, $field, $error);
    }

    public function remove(Request $request, string $field): Response
    {
        if (!in_array($field, self::FIELDS, true)) {
            return $this->ui->redirect('/dev/site');
        }

        $site = $this->site->getSite();
        $url = (string) ($site[$field . '_url'] ?? '');
        if ($url !== '') {
            $this->uploader->delete($url);
        }
        $site[$field . '_url'] = '';
        $this->site->setSite($site);

        return $this->respond($request, $field, '');
    }

    private function respond(Request $request, string $field, string $error): Response
    {
        $site = $this->site->getSite();
        $url = (string) ($site[$field . '_url'] ?? '');
        $html = MediaFieldView::render($field, $url, $this->uploader->acceptAttribute($field), $error);

        if ($this->isHx($request)) {
            return $this->responses->html($html);
        }

        $flashMessage = $error !== '' ? $error : 'Fichier mis à jour.';

        return $this->ui->withFlash($this->ui->redirect('/dev/site'), $flashMessage);
    }
}
