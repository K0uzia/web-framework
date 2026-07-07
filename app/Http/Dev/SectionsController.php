<?php

declare(strict_types=1);

namespace App\Http\Dev;

use Capsule\DevDashboard;
use Capsule\HeroStyle;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\Http\Support\FormData;
use Capsule\MediaLibrary;
use Capsule\MediaRepository;
use Capsule\Page;
use Capsule\PageRepository;
use Capsule\SectionRegistry;

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
        $page = $this->requirePage($slug);

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

        foreach ($sections as $i => $section) {
            if (!is_array($section) || ($section['id'] ?? '') !== $id) {
                continue;
            }

            if (isset($data['variant'])) {
                $type = (string) ($section['type'] ?? '');
                $sections[$i]['variant'] = $this->resolveVariant($type, (string) $data['variant']);
            }

            if (array_key_exists('visible', $data)) {
                $sections[$i]['visible'] = $data['visible'] === '1';
            }

            foreach ($data as $key => $value) {
                if (str_starts_with($key, 'content_')) {
                    $field = substr($key, 8);
                    if (preg_match('/^(items|buttons)_\d+_/', $field)) {
                        continue;
                    }
                    if (!isset($sections[$i]['content']) || !is_array($sections[$i]['content'])) {
                        $sections[$i]['content'] = [];
                    }
                    $sections[$i]['content'][$field] = $value;
                }
                if (str_starts_with($key, 'style_')) {
                    $field = substr($key, 6);
                    if (!isset($sections[$i]['style']) || !is_array($sections[$i]['style'])) {
                        $sections[$i]['style'] = [];
                    }
                    $sections[$i]['style'][$field] = $value;
                }
            }

            if ($this->hasRepeaterData($data)) {
                if (!isset($sections[$i]['content']) || !is_array($sections[$i]['content'])) {
                    $sections[$i]['content'] = [];
                }
                $sections[$i]['content']['items'] = $this->parseRepeaterItems($data);
            }

            if (array_key_exists('content_buttons_count', $data)) {
                if (!isset($sections[$i]['content']) || !is_array($sections[$i]['content'])) {
                    $sections[$i]['content'] = [];
                }
                $sections[$i]['content']['buttons'] = $this->parseButtonsRepeater($data);
            }
        }

        $this->saveSections($page, $sections);

        if ($this->isHx($request)) {
            return $this->ui->partial('section-saved.html', ['id' => $id]);
        }

        return $this->ui->redirect('/dev/pages/' . SlugCodec::encode($page->slug));
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
        $page = $this->requirePage($slug);

        return $this->respond($request, $page, '');
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
        $page = $this->requirePage($slug);

        return $this->respond($request, $page, '');
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
        $page = $this->requirePage($slug);

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
        $page = $this->requirePage($slug);

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
                );
            }
            $this->setSectionFieldValue($page, $id, $field, $url);
            $page = $this->requirePage($slug);
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
        $this->clearSectionFieldValue($page, $id, $field);
        $page = $this->requirePage($slug);

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
            $this->setSectionFieldValue($page, $id, $field, $url);
            $page = $this->requirePage($slug);
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

    private function setSectionFieldValue(Page $page, string $sectionId, string $field, string $url): void
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

            return;
        }
    }

    private function clearSectionFieldValue(Page $page, string $sectionId, string $field): void
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

            return;
        }
    }

    private function normalizeMediaField(string $field): string
    {
        $field = preg_replace('/[^a-zA-Z0-9_]/', '', $field) ?? $field;

        return in_array($field, ['image_url', 'video_url', 'url'], true) ? $field : 'image_url';
    }

    private function mediaKindForField(string $field): string
    {
        return $field === 'video_url' ? 'video' : 'image';
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

    private function requirePage(string $slug): ?Page
    {
        return $this->pages->findBySlug(SlugCodec::decode($slug), false);
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
     * @param array<string, string> $data
     */
    private function hasRepeaterData(array $data): bool
    {
        foreach ($data as $key => $_) {
            if (preg_match('/^content_items_\d+_/', $key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Regroupe les champs content_items_{i}_{champ} en éléments, avec champs
     * arbitraires selon le type de bloc. Les éléments entièrement vides sont retirés.
     *
     * @param array<string, string> $data
     *
     * @return list<array<string, string>>
     */
    private function parseRepeaterItems(array $data): array
    {
        $grouped = [];
        foreach ($data as $key => $value) {
            if (!preg_match('/^content_items_(\d+)_([a-zA-Z0-9_]+)$/', $key, $m)) {
                continue;
            }
            $grouped[(int) $m[1]][$m[2]] = $value;
        }

        ksort($grouped);

        $items = [];
        foreach ($grouped as $item) {
            $hasContent = false;
            foreach ($item as $value) {
                if (trim($value) !== '') {
                    $hasContent = true;
                    break;
                }
            }
            if ($hasContent) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * @param array<string, string> $data
     *
     * @return list<array{label: string, href: string, style: string}>
     */
    private function parseButtonsRepeater(array $data): array
    {
        $buttons = [];
        $i = 0;
        while (array_key_exists('content_buttons_' . $i . '_label', $data) || array_key_exists('content_buttons_' . $i . '_href', $data)) {
            $label = trim($data['content_buttons_' . $i . '_label'] ?? '');
            $href = trim($data['content_buttons_' . $i . '_href'] ?? '');
            $style = ($data['content_buttons_' . $i . '_style'] ?? 'primary') === 'secondary' ? 'secondary' : 'primary';
            if ($label !== '' || $href !== '') {
                $buttons[] = ['label' => $label, 'href' => $href, 'style' => $style];
            }
            $i++;
        }

        return $buttons;
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
        $variants = $this->registry->getVariants($type);
        $keys = array_map('strval', array_keys($variants));
        if ($requested !== '' && in_array($requested, $keys, true)) {
            return $requested;
        }

        return $keys[0] ?? 'default';
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
