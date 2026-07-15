<?php

declare(strict_types=1);

namespace App\Http\Dev;

use Capsule\FooterStyle;

/**
 * Formulaire d'édition des pieds de page shadcnblocks (footer2, footer7).
 */
final class FooterFormRenderer
{
    /**
     * @param array<string, mixed> $variant
     */
    public function render(array $variant): string
    {
        $template = FooterStyle::normalizeTemplate((string) ($variant['template'] ?? FooterStyle::TEMPLATE_DEFAULT));
        $maxSections = $template === 'footer7' ? 3 : 4;
        $sections = is_array($variant['sections'] ?? null) ? $variant['sections'] : [];
        $legalLinks = is_array($variant['legal_links'] ?? null) ? $variant['legal_links'] : [];
        $socialLinks = is_array($variant['social_links'] ?? null) ? $variant['social_links'] : [];

        $html = '<div class="dev-panel dev-panel--compact">'
            . '<h2 class="dev-panel__title">Contenu du pied de page</h2>'
            . '<div class="dev-field">'
            . '<label class="dev-label" for="footer_description">Description sous le logo</label>'
            . '<textarea class="dev-input dev-textarea" id="footer_description" name="footer_description" rows="3">'
            . htmlspecialchars((string) ($variant['description'] ?? ''), ENT_QUOTES) . '</textarea>'
            . '</div></div>';

        for ($i = 0; $i < $maxSections; $i++) {
            $section = is_array($sections[$i] ?? null) ? $sections[$i] : [];
            $html .= $this->sectionFields($i, $section);
        }

        $html .= '<div class="dev-panel dev-panel--compact"><h2 class="dev-panel__title">Liens légaux</h2><div class="dev-form--grid dev-form--grid-2">';
        for ($i = 0; $i < 2; $i++) {
            $link = is_array($legalLinks[$i] ?? null) ? $legalLinks[$i] : [];
            $html .= $this->linkPairFields('legal_' . $i, (string) ($link['label'] ?? ''), (string) ($link['href'] ?? ''));
        }
        $html .= '</div></div>';

        if ($template === 'footer7') {
            $html .= '<div class="dev-panel dev-panel--compact"><h2 class="dev-panel__title">Réseaux sociaux</h2><div class="dev-form--grid dev-form--grid-2">';
            $networks = ['instagram', 'facebook', 'x', 'linkedin', 'github'];
            foreach ($networks as $i => $network) {
                $link = is_array($socialLinks[$i] ?? null) ? $socialLinks[$i] : ['network' => $network, 'href' => ''];
                $html .= '<div class="dev-field dev-form__full">'
                    . '<label class="dev-label" for="social_' . $i . '_href">' . ucfirst($network) . '</label>'
                    . '<input type="hidden" name="social_' . $i . '_network" value="' . htmlspecialchars($network, ENT_QUOTES) . '" />'
                    . '<input class="dev-input" id="social_' . $i . '_href" type="text" name="social_' . $i . '_href" value="'
                    . htmlspecialchars((string) ($link['href'] ?? ''), ENT_QUOTES) . '" placeholder="https://… ou #" />'
                    . '</div>';
            }
            $html .= '</div></div>';
        }

        return $html;
    }

    /**
     * @param array<string, mixed> $section
     */
    private function sectionFields(int $index, array $section): string
    {
        $linksText = '';
        $links = is_array($section['links'] ?? null) ? $section['links'] : [];
        foreach ($links as $link) {
            if (!is_array($link)) {
                continue;
            }
            $label = trim((string) ($link['label'] ?? $link['name'] ?? ''));
            $href = trim((string) ($link['href'] ?? ''));
            if ($label !== '') {
                $linksText .= $label . ' | ' . ($href !== '' ? $href : '#') . "\n";
            }
        }

        return '<div class="dev-panel dev-panel--compact">'
            . '<h2 class="dev-panel__title">Colonne ' . ($index + 1) . '</h2>'
            . '<div class="dev-field">'
            . '<label class="dev-label" for="section_' . $index . '_title">Titre</label>'
            . '<input class="dev-input" id="section_' . $index . '_title" type="text" name="section_' . $index . '_title" value="'
            . htmlspecialchars((string) ($section['title'] ?? ''), ENT_QUOTES) . '" />'
            . '</div>'
            . '<div class="dev-field">'
            . '<label class="dev-label" for="section_' . $index . '_links">Liens</label>'
            . '<textarea class="dev-input dev-textarea" id="section_' . $index . '_links" name="section_' . $index . '_links" rows="5" placeholder="Libellé | /url">'
            . htmlspecialchars(rtrim($linksText), ENT_QUOTES) . '</textarea>'
            . '<span class="dev-hint">Un lien par ligne, format : <code>Libellé | /chemin</code></span>'
            . '</div></div>';
    }

    private function linkPairFields(string $prefix, string $label, string $href): string
    {
        return '<div class="dev-field">'
            . '<label class="dev-label" for="' . $prefix . '_label">Libellé</label>'
            . '<input class="dev-input" id="' . $prefix . '_label" type="text" name="' . $prefix . '_label" value="'
            . htmlspecialchars($label, ENT_QUOTES) . '" />'
            . '</div>'
            . '<div class="dev-field">'
            . '<label class="dev-label" for="' . $prefix . '_href">Lien</label>'
            . '<input class="dev-input" id="' . $prefix . '_href" type="text" name="' . $prefix . '_href" value="'
            . htmlspecialchars($href, ENT_QUOTES) . '" />'
            . '</div>';
    }

    /**
     * @param array<string, string> $data
     *
     * @return array<string, mixed>
     */
    public function parseForm(array $data, string $template): array
    {
        $template = FooterStyle::normalizeTemplate($template);
        $maxSections = $template === 'footer7' ? 3 : 4;
        $sections = [];
        for ($i = 0; $i < $maxSections; $i++) {
            $title = trim($data['section_' . $i . '_title'] ?? '');
            $links = self::parseLinksText($data['section_' . $i . '_links'] ?? '');
            if ($title !== '' || $links !== []) {
                $sections[] = ['title' => $title, 'links' => $links];
            }
        }

        $legalLinks = [];
        for ($i = 0; $i < 2; $i++) {
            $label = trim($data['legal_' . $i . '_label'] ?? '');
            $href = trim($data['legal_' . $i . '_href'] ?? '');
            if ($label !== '') {
                $legalLinks[] = ['label' => $label, 'href' => $href !== '' ? $href : '#'];
            }
        }

        $socialLinks = [];
        if ($template === 'footer7') {
            for ($i = 0; $i < 5; $i++) {
                $network = trim($data['social_' . $i . '_network'] ?? '');
                $href = trim($data['social_' . $i . '_href'] ?? '');
                if ($network !== '' && $href !== '') {
                    $socialLinks[] = ['network' => $network, 'href' => $href];
                }
            }
        }

        return [
            'description' => trim($data['footer_description'] ?? ''),
            'sections' => $sections,
            'legal_links' => $legalLinks,
            'social_links' => $socialLinks,
        ];
    }

    /**
     * @return list<array{label: string, href: string}>
     */
    private static function parseLinksText(string $text): array
    {
        $links = [];
        foreach (preg_split('/\r\n|\n|\r/', $text) ?: [] as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $parts = array_map('trim', explode('|', $line, 2));
            $label = $parts[0] ?? '';
            $href = $parts[1] ?? '#';
            if ($label !== '') {
                $links[] = ['label' => $label, 'href' => $href !== '' ? $href : '#'];
            }
        }

        return $links;
    }

    /**
     * @param array<string, mixed> $variant
     */
    public function renderDefaultLegalLinks(array $variant): string
    {
        $legalLinks = is_array($variant['legal_links'] ?? null) ? $variant['legal_links'] : [];
        $html = '<div class="dev-form--grid dev-form--grid-2 dev-chrome-fields">';
        for ($i = 0; $i < 2; $i++) {
            $link = is_array($legalLinks[$i] ?? null) ? $legalLinks[$i] : [];
            $html .= $this->linkPairFields('legal_' . $i, (string) ($link['label'] ?? ''), (string) ($link['href'] ?? ''));
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * @param array<string, string> $data
     *
     * @return list<array{label: string, href: string}>
     */
    public function parseDefaultLegalLinks(array $data): array
    {
        $legalLinks = [];
        for ($i = 0; $i < 2; $i++) {
            $label = trim($data['legal_' . $i . '_label'] ?? '');
            $href = trim($data['legal_' . $i . '_href'] ?? '');
            if ($label !== '') {
                $legalLinks[] = ['label' => $label, 'href' => $href !== '' ? $href : '#'];
            }
        }

        return $legalLinks;
    }
}
