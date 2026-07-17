<?php

declare(strict_types=1);

namespace App\Http\Dev;

use App\Http\Dev\Sections\ClientAccessKinds;
use App\Http\Dev\Sections\SectionFormRenderer;
use App\Http\Dev\Sections\SectionDefaults;
use Capsule\ClientDashboardConfig;
use Capsule\DevDashboard;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\Http\Support\FormData;
use Capsule\MediaLibrary;
use Capsule\MediaRepository;
use Capsule\Page;
use Capsule\PageRepository;
use Capsule\Section\SectionFieldSchema;
use Capsule\Section\SectionVariantResolver;
use Capsule\SectionRegistry;
use Capsule\SiteRepository;
use Capsule\HeroStyle;

final class SectionsController
{
    use DevHx;

    public function __construct(
        private readonly DevDashboard $ui,
        private readonly PageRepository $pages,
        private readonly SectionRegistry $registry,
        private readonly SectionFormRenderer $sectionForms,
        private readonly MediaUploader $uploader,
        private readonly LibraryMediaUploader $libraryUploader,
        private readonly MediaLibrary $mediaLibrary,
        private readonly MediaRepository $mediaRepository,
        private readonly SectionVariantResolver $variantResolver,
        private readonly SectionFieldSchema $fieldSchema,
        private readonly SiteRepository $site,
    ) {
    }

    public function add(Request $request, string $slug): Response
    {
        $page = $this->requirePage($slug);
        if ($page === null) {
            return $this->ui->redirect('/dev/pages');
        }

        $data = FormData::fromRequest($request);
        $type = trim($data['type'] ?? '');
        if ($type === '') {
            return $this->respond($request, $page, '');
        }

        $variant = $this->resolveVariant($type, trim($data['variant'] ?? ''));

        $sections = $page->sections;
        $sections[] = [
            'id' => $type . '-' . bin2hex(random_bytes(3)),
            'type' => $type,
            'variant' => $variant,
            'visible' => true,
            'content' => $this->defaultContent($type, $variant),
            'style' => $this->defaultStyle($type, $variant),
        ];

        $this->saveSections($page, $sections);
        $page = $this->pageWithSections($page, $sections);

        return $this->respond($request, $page, 'Section ajoutée.');
    }

    public function update(Request $request, string $slug, string $id): Response
    {
        $page = $this->requirePage($slug);
        if ($page === null) {
            return $this->ui->redirect('/dev/pages');
        }

        $data = FormData::fromRequest($request);
        $sections = $page->sections;
        $updatedSection = null;
        $variantChanged = false;
        $previousVariant = '';

        foreach ($sections as $i => $section) {
            if (!is_array($section) || ($section['id'] ?? '') !== $id) {
                continue;
            }

            $type = (string) ($section['type'] ?? '');
            $variant = (string) ($sections[$i]['variant'] ?? '');
            $previousVariant = $variant;

            if (isset($data['variant'])) {
                $variant = $this->resolveVariant($type, (string) $data['variant']);
                $variantChanged = $variant !== $previousVariant;
                $sections[$i]['variant'] = $variant;
            }

            if (array_key_exists('visible', $data)) {
                $sections[$i]['visible'] = $data['visible'] === '1';
            }

            $content = is_array($sections[$i]['content'] ?? null) ? $sections[$i]['content'] : [];
            $style = is_array($sections[$i]['style'] ?? null) ? $sections[$i]['style'] : [];
            $sections[$i]['content'] = $this->fieldSchema->unflattenForm($data, $content, $type, $variant);
            $sections[$i]['style'] = $this->fieldSchema->unflattenStyleForm($data, $style, $type);
            $updatedSection = $sections[$i];
        }

        $this->saveSections($page, $sections);

        if ($variantChanged && is_array($updatedSection)) {
            $this->remapClientAccessForVariant($page->slug, $id, $updatedSection, $previousVariant);
        }

        if ($this->isHx($request) && ($data['variant_refresh'] ?? '') === '1' && is_array($updatedSection)) {
            $html = $this->sectionForms->renderSectionBody($slug, $updatedSection);
            $type = (string) ($updatedSection['type'] ?? '');
            $variant = (string) ($updatedSection['variant'] ?? '');

            return $this->ui->fragment($html)
                ->withHeader('X-Dev-Variant-Label', $this->sectionForms->resolvedVariantLabel($type, $variant));
        }

        if ($this->isHx($request)) {
            return $this->ui->partial('section-saved.html', ['id' => $id]);
        }

        return $this->ui->redirect('/dev/pages/' . SlugCodec::encode($page->slug));
    }

    public function updateClientAccess(Request $request, string $slug, string $id): Response
    {
        $page = $this->requirePage($slug);
        if ($page === null) {
            return $this->ui->redirect('/dev/pages');
        }

        $section = null;
        foreach ($page->sections as $item) {
            if (is_array($item) && ($item['id'] ?? '') === $id) {
                $section = $item;
                break;
            }
        }
        if ($section === null) {
            return $this->ui->redirect('/dev/pages/' . SlugCodec::encode($page->slug));
        }

        $type = (string) ($section['type'] ?? '');
        $variant = $this->resolveVariant($type, (string) ($section['variant'] ?? ''));
        $fields = $this->fieldSchema->contentFieldsForVariant($type, $variant);
        $groups = ClientAccessKinds::groupFieldKeys($fields);

        $data = FormData::fromRequest($request);
        $perms = [
            'editableText' => ($data['editable_text'] ?? '0') === '1',
            'editableImage' => ($data['editable_image'] ?? '0') === '1',
            'editableLink' => ($data['editable_link'] ?? '0') === '1',
        ];
        $newFields = ClientAccessKinds::allowedFromPermissions($groups, $perms);

        $config = $this->site->getClientDashboard();
        $pages = $config['pages'];
        if ($newFields === []) {
            unset($pages[$page->slug]['sections'][$id]);
            if (($pages[$page->slug]['sections'] ?? []) === []) {
                unset($pages[$page->slug]);
            }
        } else {
            $pages[$page->slug]['sections'][$id] = ['fields' => $newFields];
        }
        $this->site->setClientDashboard([
            'medias_enabled' => ClientDashboardConfig::isMediasEnabled($config),
            'pages' => $pages,
        ]);

        if ($this->isHx($request)) {
            return $this->ui->partial('saved.html', ['message' => 'Accès client enregistré']);
        }

        return $this->ui->withFlash(
            $this->ui->redirect('/dev/pages/' . SlugCodec::encode($page->slug)),
            'Accès client enregistré.',
        );
    }

    public function move(Request $request, string $slug, string $id): Response
    {
        $page = $this->requirePage($slug);
        if ($page === null) {
            return $this->ui->redirect('/dev/pages');
        }

        $data = FormData::fromRequest($request);
        $direction = $data['direction'] ?? '';
        $sections = $page->sections;
        $index = $this->findSectionIndex($sections, $id);
        if ($index < 0) {
            return $this->respond($request, $page, '');
        }

        $swap = $direction === 'up' ? $index - 1 : $index + 1;
        if ($swap < 0 || $swap >= count($sections)) {
            return $this->respond($request, $page, '');
        }

        [$sections[$index], $sections[$swap]] = [$sections[$swap], $sections[$index]];
        $this->saveSections($page, $sections);

        return $this->respondLightweight($request, $page);
    }

    public function reorder(Request $request, string $slug): Response
    {
        $page = $this->requirePage($slug);
        if ($page === null) {
            return $this->ui->redirect('/dev/pages');
        }

        $data = FormData::fromRequest($request);
        $order = $this->parseOrder($data);
        $sections = $page->sections;

        if ($order !== []) {
            $byId = [];
            foreach ($sections as $section) {
                if (is_array($section) && isset($section['id'])) {
                    $byId[(string) $section['id']] = $section;
                }
            }

            $reordered = [];
            foreach ($order as $id) {
                if (isset($byId[$id])) {
                    $reordered[] = $byId[$id];
                    unset($byId[$id]);
                }
            }
            foreach ($byId as $remaining) {
                $reordered[] = $remaining;
            }

            $sections = $reordered;
        }

        $this->saveSections($page, $sections);

        return $this->respondLightweight($request, $page);
    }

    /**
     * @param array<string, string> $data
     *
     * @return list<string>
     */
    private function parseOrder(array $data): array
    {
        $raw = $data['order'] ?? '';
        if ($raw === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $raw)), static fn ($v) => $v !== ''));
    }

    public function destroy(Request $request, string $slug, string $id): Response
    {
        $page = $this->requirePage($slug);
        if ($page === null) {
            return $this->ui->redirect('/dev/pages');
        }

        $sections = array_values(array_filter(
            $page->sections,
            static fn ($section): bool => is_array($section) && ($section['id'] ?? '') !== $id,
        ));

        $this->saveSections($page, $sections);
        $page = $this->pageWithSections($page, $sections);

        return $this->respond($request, $page, '');
    }

    public function restore(Request $request, string $slug): Response
    {
        $page = $this->requirePage($slug);
        if ($page === null) {
            return $this->ui->redirect('/dev/pages');
        }

        $data = FormData::fromRequest($request);
        $section = $this->parseSectionPayload($data['section'] ?? '');
        if ($section === null) {
            return $this->respond($request, $page, '');
        }

        $id = (string) ($section['id'] ?? '');
        if ($id === '' || $this->findSectionIndex($page->sections, $id) >= 0) {
            return $this->respond($request, $page, '');
        }

        $index = max(0, min((int) ($data['index'] ?? 0), count($page->sections)));
        $sections = $page->sections;
        array_splice($sections, $index, 0, [$section]);

        $this->saveSections($page, $sections);
        $page = $this->pageWithSections($page, $sections);

        return $this->respond($request, $page, '');
    }

    public function uploadMedia(Request $request, string $slug, string $id, string $field): Response
    {
        $page = $this->requirePage($slug);
        if ($page === null) {
            return $this->ui->redirect('/dev/pages');
        }

        $field = $this->normalizeMediaField($field);
        $kind = $this->mediaKindForField($field);
        $error = '';

        try {
            $file = $request->files['file'] ?? null;
            if (!is_array($file)) {
                throw new MediaUploadException('Aucun fichier reçu.');
            }
            $url = $kind === 'video'
                ? $this->libraryUploader->storeVideo($file)
                : $this->libraryUploader->storeImage($file);
            if ($this->mediaRepository->findByUrl($url) === null) {
                $this->mediaRepository->create(
                    $kind,
                    $url,
                    basename($url),
                    (string) ($file['type'] ?? ''),
                    (int) ($file['size'] ?? 0),
                    '',
                    \Capsule\MediaRepository::OWNER_DEV,
                );
            }
            $page = $this->setSectionFieldValue($page, $id, $field, $url);
        } catch (MediaUploadException $e) {
            $error = $e->getMessage();
        }

        return $this->respondMediaField($request, $page, $id, $field, $error);
    }

    public function removeMedia(Request $request, string $slug, string $id, string $field): Response
    {
        $page = $this->requirePage($slug);
        if ($page === null) {
            return $this->ui->redirect('/dev/pages');
        }

        $field = $this->normalizeMediaField($field);
        $page = $this->clearSectionFieldValue($page, $id, $field);

        return $this->respondMediaField($request, $page, $id, $field, '');
    }

    public function selectMedia(Request $request, string $slug, string $id, string $field): Response
    {
        $page = $this->requirePage($slug);
        if ($page === null) {
            return $this->ui->redirect('/dev/pages');
        }

        $field = $this->normalizeMediaField($field);
        $kind = $this->mediaKindForField($field);
        $data = FormData::fromRequest($request);
        $url = trim($data['url'] ?? '');
        $error = '';
        if ($url === '' || !$this->mediaLibrary->isAllowedUrl($url, $kind)) {
            $error = 'URL de média non autorisée.';
        } else {
            $page = $this->setSectionFieldValue($page, $id, $field, $url);
        }

        return $this->respondMediaField($request, $page, $id, $field, $error);
    }

    /**
     * @param array<string, string> $data
     *
     * @return array<string, mixed>|null
     */
    private function parseSectionPayload(string $raw): ?array
    {
        if ($raw === '') {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded) || !isset($decoded['id'], $decoded['type'])) {
            return null;
        }

        if (!isset($decoded['content']) || !is_array($decoded['content'])) {
            $decoded['content'] = [];
        }
        if (!isset($decoded['style']) || !is_array($decoded['style'])) {
            $decoded['style'] = [];
        }
        if (!isset($decoded['variant'])) {
            $decoded['variant'] = 'default';
        }
        if (!isset($decoded['visible'])) {
            $decoded['visible'] = true;
        }

        return $decoded;
    }

    private function respondMediaField(Request $request, ?Page $page, string $sectionId, string $field, string $error): Response
    {
        if ($page === null) {
            return $this->ui->redirect('/dev/pages');
        }

        $section = null;
        foreach ($page->sections as $candidate) {
            if (is_array($candidate) && ($candidate['id'] ?? '') === $sectionId) {
                $section = $candidate;
                break;
            }
        }

        if ($section === null) {
            return $this->ui->redirect('/dev/pages/' . SlugCodec::encode($page->slug));
        }

        $html = $this->sectionForms->renderMediaField(
            SlugCodec::encode($page->slug),
            $section,
            $field,
            $error,
        );

        if ($this->isHx($request)) {
            return $this->ui->fragment($html);
        }

        return $this->ui->withFlash(
            $this->ui->redirect('/dev/pages/' . SlugCodec::encode($page->slug)),
            $error !== '' ? $error : 'Média mis à jour.',
        );
    }

    private function setSectionFieldValue(Page $page, string $sectionId, string $field, string $url): Page
    {
        $sections = $page->sections;
        foreach ($sections as $i => $section) {
            if (!is_array($section) || ($section['id'] ?? '') !== $sectionId) {
                continue;
            }
            if (!isset($sections[$i]['content']) || !is_array($sections[$i]['content'])) {
                $sections[$i]['content'] = [];
            }
            $sections[$i]['content'][$field] = $url;
            $this->saveSections($page, $sections);

            return $this->pageWithSections($page, $sections);
        }

        return $page;
    }

    private function clearSectionFieldValue(Page $page, string $sectionId, string $field): Page
    {
        $sections = $page->sections;
        foreach ($sections as $i => $section) {
            if (!is_array($section) || ($section['id'] ?? '') !== $sectionId) {
                continue;
            }
            if (!isset($sections[$i]['content']) || !is_array($sections[$i]['content'])) {
                $sections[$i]['content'] = [];
            }
            $sections[$i]['content'][$field] = '';
            $this->saveSections($page, $sections);

            return $this->pageWithSections($page, $sections);
        }

        return $page;
    }

    private function normalizeMediaField(string $field): string
    {
        $field = preg_replace('/[^a-zA-Z0-9_]/', '', $field) ?? $field;
        $allowed = ['image_url', 'video_url', 'background_image_url', 'background_video_url', 'url'];

        return in_array($field, $allowed, true) ? $field : 'image_url';
    }

    private function mediaKindForField(string $field): string
    {
        return in_array($field, ['video_url', 'background_video_url'], true) ? 'video' : 'image';
    }

    private function respond(Request $request, ?Page $page, string $flash): Response
    {
        if ($page === null) {
            return $this->ui->redirect('/dev/pages');
        }

        if ($this->isHx($request)) {
            return $this->ui->partial('sections-list.html', [
                'sections_html' => $this->sectionForms->renderAll($page),
            ]);
        }

        if ($flash !== '') {
            return $this->ui->withFlash(
                $this->ui->redirect('/dev/pages/' . SlugCodec::encode($page->slug)),
                $flash,
            );
        }

        return $this->ui->redirect('/dev/pages/' . SlugCodec::encode($page->slug));
    }

    private function respondLightweight(Request $request, ?Page $page): Response
    {
        if ($page === null) {
            return $this->ui->redirect('/dev/pages');
        }

        if ($this->isHx($request)) {
            return $this->ui->fragment('');
        }

        return $this->ui->redirect('/dev/pages/' . SlugCodec::encode($page->slug));
    }

    /**
     * @param list<array<string, mixed>> $sections
     */
    private function pageWithSections(Page $page, array $sections): Page
    {
        return new Page(
            slug: $page->slug,
            title: $page->title,
            layout: $page->layout,
            description: $page->description,
            sections: $sections,
            meta: $page->meta,
            published: $page->published,
            updatedAt: $page->updatedAt,
        );
    }

    private function requirePage(string $slug): ?Page
    {
        return $this->pages->findBySlug(SlugCodec::decode($slug), false);
    }

    /**
     * Recalcule les champs Accès Client après un changement de variante
     * (mêmes permissions texte / image / lien, champs adaptés à la nouvelle variante).
     *
     * @param array<string, mixed> $section
     */
    private function remapClientAccessForVariant(
        string $pageSlug,
        string $sectionId,
        array $section,
        string $previousVariant,
    ): void {
        $config = $this->site->getClientDashboard();
        $stored = ClientDashboardConfig::allowedFields($config, $pageSlug, $sectionId);
        if ($stored === []) {
            return;
        }

        $type = (string) ($section['type'] ?? '');
        $variant = (string) ($section['variant'] ?? '');
        if ($type === '') {
            return;
        }

        $oldFields = $this->fieldSchema->contentFieldsForVariant($type, $previousVariant);
        $newFields = $this->fieldSchema->contentFieldsForVariant($type, $variant);
        $oldGroups = ClientAccessKinds::groupFieldKeys($oldFields);
        $newGroups = ClientAccessKinds::groupFieldKeys($newFields);
        $perms = ClientAccessKinds::permissionsFromAllowed($oldGroups, $stored);
        $newAllowed = ClientAccessKinds::allowedFromPermissions($newGroups, $perms);

        $pages = $config['pages'];
        if ($newAllowed === []) {
            unset($pages[$pageSlug]['sections'][$sectionId]);
            if (($pages[$pageSlug]['sections'] ?? []) === []) {
                unset($pages[$pageSlug]);
            }
        } else {
            $pages[$pageSlug]['sections'][$sectionId] = ['fields' => $newAllowed];
        }

        $this->site->setClientDashboard([
            'medias_enabled' => ClientDashboardConfig::isMediasEnabled($config),
            'pages' => $pages,
        ]);
    }

    /**
     * @param list<array<string, mixed>> $sections
     */
    private function saveSections(Page $page, array $sections): void
    {
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
    }

    /**
     * @param list<array<string, mixed>> $sections
     */
    private function findSectionIndex(array $sections, string $id): int
    {
        foreach ($sections as $i => $section) {
            if (is_array($section) && ($section['id'] ?? '') === $id) {
                return $i;
            }
        }

        return -1;
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultContent(string $type, string $variant = ''): array
    {
        return SectionDefaults::content($type, $variant);
    }

    private function resolveVariant(string $type, string $requested): string
    {
        return $this->variantResolver->resolve($type, $requested);
    }

    /**
     * @return array<string, string>
     */
    private function defaultStyle(string $type, string $variant = ''): array
    {
        if ($type === 'hero' && $variant !== '') {
            return HeroStyle::defaults($variant);
        }

        return SectionDefaults::style($type);
    }
}
