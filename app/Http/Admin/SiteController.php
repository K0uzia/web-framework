<?php

declare(strict_types=1);

namespace App\Http\Admin;

use App\Http\Dev\MediaUploadException;
use App\Http\Dev\MediaUploader;
use Capsule\AdminDashboard;
use Capsule\ClientDashboardConfig;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\Http\Support\FormData;
use Capsule\MediaLibrary;
use Capsule\MediaRepository;
use Capsule\SiteRepository;

final class SiteController
{
    public function __construct(
        private readonly AdminDashboard $ui,
        private readonly SiteRepository $site,
        private readonly MediaLibrary $mediaLibrary,
        private readonly MediaRepository $media,
        private readonly MediaUploader $uploader,
        private readonly SiteContactSync $contactSync,
    ) {
    }

    public function edit(Request $request): Response
    {
        $denied = $this->guardSite();
        if ($denied !== null) {
            return $denied;
        }

        $site = $this->site->getSite();
        $seed = $this->contactSync->seedFromPages(
            trim((string) ($site['contact_email'] ?? '')),
            trim((string) ($site['contact_phone'] ?? '')),
            trim((string) ($site['contact_address'] ?? '')),
        );
        $mediasEnabled = ClientDashboardConfig::isMediasEnabled($this->site->getClientDashboard());
        if ($mediasEnabled) {
            $this->mediaLibrary->syncClientRecords('image');
        }

        return $this->ui->render('site-edit.html', [
            'title' => 'Identité et contact',
            'nav_section' => 'site',
            'flash' => $this->ui->flashFromRequest($request),
            'form_action' => '/admin/site',
            'site_name' => (string) ($site['name'] ?? ''),
            'site_tagline' => (string) ($site['tagline'] ?? ''),
            'contact_email' => $seed['email'],
            'contact_phone' => $seed['phone'],
            'contact_address' => $seed['address'],
            'logo_picker_html' => $this->renderMediaField(
                'logo_url',
                'Logo',
                (string) ($site['logo_url'] ?? ''),
                $mediasEnabled,
            ),
            'favicon_picker_html' => $this->renderMediaField(
                'favicon_url',
                'Favicon',
                (string) ($site['favicon_url'] ?? ''),
                $mediasEnabled,
            ),
            'logo_upload_accept' => $this->uploader->acceptAttribute('logo'),
            'favicon_upload_accept' => $this->uploader->acceptAttribute('favicon'),
        ]);
    }

    public function update(Request $request): Response
    {
        $denied = $this->guardSite();
        if ($denied !== null) {
            return $denied;
        }

        $data = FormData::fromRequest($request);
        $site = $this->site->getSite();

        $site['name'] = trim($data['site_name'] ?? (string) ($site['name'] ?? ''));
        $site['tagline'] = trim($data['site_tagline'] ?? (string) ($site['tagline'] ?? ''));
        $site['logo_url'] = trim($data['logo_url'] ?? (string) ($site['logo_url'] ?? ''));
        $site['favicon_url'] = trim($data['favicon_url'] ?? (string) ($site['favicon_url'] ?? ''));

        $email = trim($data['contact_email'] ?? '');
        $phone = trim($data['contact_phone'] ?? '');
        $address = trim($data['contact_address'] ?? '');
        $site['contact_email'] = $email;
        $site['contact_phone'] = $phone;
        $site['contact_address'] = $address;

        $this->site->setSite($site);
        $this->contactSync->propagateToContactSections($email, $phone, $address);

        return $this->ui->withFlash(
            $this->ui->redirect('/admin/site'),
            'Identité et contact enregistrés.',
        );
    }

    public function upload(Request $request, string $field): Response
    {
        $denied = $this->guardSite();
        if ($denied !== null) {
            return $denied;
        }

        $map = ['logo' => 'logo_url', 'favicon' => 'favicon_url'];
        if (!isset($map[$field])) {
            return $this->ui->redirect('/admin/site');
        }

        $file = $request->files['file'] ?? null;
        try {
            if (!is_array($file)) {
                throw new MediaUploadException('Aucun fichier reçu.');
            }
            $url = $this->uploader->store($field, $file);
            $site = $this->site->getSite();
            $site[$map[$field]] = $url;
            $this->site->setSite($site);

            return $this->ui->withFlash($this->ui->redirect('/admin/site'), 'Fichier importé.');
        } catch (MediaUploadException $e) {
            return $this->ui->withFlash($this->ui->redirect('/admin/site'), $e->getMessage());
        }
    }

    private function guardSite(): ?Response
    {
        if (ClientDashboardConfig::isSiteEnabled($this->site->getClientDashboard())) {
            return null;
        }

        return $this->ui->withFlash(
            $this->ui->redirect('/admin/home'),
            'L\'édition de l\'identité n\'est pas ouverte.',
        );
    }

    private function renderMediaField(string $name, string $label, string $value, bool $mediasEnabled): string
    {
        $safeName = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeValue = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $id = 'site-' . preg_replace('/[^a-zA-Z0-9_-]/', '', $name);

        if (!$mediasEnabled) {
            $preview = $value !== ''
                ? '<img class="admin-site-media__preview" src="' . $safeValue . '" alt="" width="120" height="80" />'
                : '';

            return '<div class="admin-field">'
                . '<label class="admin-label" for="' . $id . '">' . $safeLabel . '</label>'
                . $preview
                . '<input class="admin-input" id="' . $id . '" type="url" name="' . $safeName . '" value="' . $safeValue . '" placeholder="/uploads/…" />'
                . '</div>';
        }

        $urls = $this->imageUrls();
        $picks = [];
        $seen = [];
        if ($value !== '') {
            $picks[] = $this->pickButton($value, true);
            $seen[$value] = true;
        }
        foreach ($urls as $url) {
            if (isset($seen[$url])) {
                continue;
            }
            $picks[] = $this->pickButton($url, $url === $value);
            $seen[$url] = true;
        }

        $grid = $picks === []
            ? '<p class="admin-media-picker__empty">Aucune image dans votre bibliothèque.</p>'
            : '<div class="admin-media-picker__grid" role="listbox" aria-label="' . $safeLabel . '">'
                . implode('', $picks)
                . '</div>';

        $noneSelected = $value === '' ? ' is-selected' : '';

        return '<div class="admin-field admin-media-picker" data-admin-media-picker>'
            . '<span class="admin-label" id="' . $id . '-label">' . $safeLabel . '</span>'
            . '<input type="hidden" id="' . $id . '" name="' . $safeName . '" value="' . $safeValue . '" data-admin-media-value />'
            . $grid
            . '<div class="admin-media-picker__actions">'
            . '<button type="button" class="admin-btn admin-btn--ghost admin-btn--sm' . $noneSelected . '" data-admin-media-clear'
            . ' aria-pressed="' . ($value === '' ? 'true' : 'false') . '">Aucune image</button>'
            . '<a class="admin-btn admin-btn--soft admin-btn--sm" href="/admin/medias">'
            . '<i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i> Bibliothèque</a>'
            . '</div></div>';
    }

    private function pickButton(string $url, bool $selected): string
    {
        $safeUrl = htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $name = htmlspecialchars(basename($url), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $cls = $selected ? ' is-selected' : '';

        return '<button type="button" class="admin-media-picker__pick' . $cls . '" role="option"'
            . ' data-admin-media-pick data-url="' . $safeUrl . '"'
            . ' aria-selected="' . ($selected ? 'true' : 'false') . '"'
            . ' aria-label="Choisir ' . $name . '" title="' . $name . '">'
            . '<img class="admin-media-picker__thumb" src="' . $safeUrl . '" alt="" width="96" height="72" loading="lazy" />'
            . '</button>';
    }

    /**
     * @return list<string>
     */
    private function imageUrls(): array
    {
        $out = [];
        foreach ($this->media->all('image', MediaRepository::OWNER_CLIENT) as $row) {
            $url = is_string($row['url'] ?? null) ? $row['url'] : '';
            if ($url !== '') {
                $out[] = $url;
            }
        }

        return $out;
    }
}
