<?php

declare(strict_types=1);

namespace Capsule;

use Capsule\Section\SectionItemsTrait;

/**
 * Rendu HTML spécifique aux variantes demo (conversion book-a-demo shadcnblocks).
 */
final class DemoVariantRenderer
{
    use SectionItemsTrait;

    private const SHARED_TYPE = 'demo';

    /** @var array<string, int> */
    private const MAX_ITEMS = [
        'bookademo1' => 8,
        'bookademo2' => 6,
    ];

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    public static function enrich(array $data, array $content, string $variant): array
    {
        return match ($variant) {
            'bookademo2' => self::enrichBookademo2($data, $content),
            default => self::enrichBookademo1($data, $content),
        };
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichBookademo1(array $data, array $content): array
    {
        $data['demo_benefits_html'] = self::benefitsHtml($content);
        $data['demo_logos_html'] = self::marqueeHtml($content, 'bookademo1');
        $data['demo_form_html'] = self::formBookademo1Html($content);

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichBookademo2(array $data, array $content): array
    {
        $data['demo_header_html'] = self::headerBookademo2Html($content);
        $data['demo_form_html'] = self::formBookademo2Html($content);
        $data['demo_testimonials_html'] = self::testimonialsBookademo2Html($content);
        $data['demo_footer_html'] = self::footerBookademo2Html($content);

        return $data;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function benefitsHtml(array $content): string
    {
        $items = self::itemsFromContent($content, 3);
        $count = count($items);
        $html = '<ul class="section-demo__benefits--bookademo1">';
        foreach ($items as $index => $item) {
            $text = trim((string) ($item['text'] ?? ''));
            if ($text === '') {
                continue;
            }
            $modifiers = ['section-demo__benefit--bookademo1'];
            if ($index === $count - 1) {
                $modifiers[] = 'section-demo__benefit--last--bookademo1';
            }
            $html .= '<li class="' . implode(' ', $modifiers) . '">'
                . '<i class="fa-solid fa-arrow-right section-demo__benefit-icon--bookademo1" aria-hidden="true"></i>'
                . '<p class="section-demo__benefit-text--bookademo1">'
                . htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                . '</p></li>';
        }
        $html .= '</ul>';

        return $html;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function marqueeHtml(array $content, string $variant): string
    {
        $logos = is_array($content['logos'] ?? null) ? $content['logos'] : [];
        if ($logos === []) {
            $logos = array_map(
                static fn (int $i): array => ['url' => SectionAssets::shared(self::SHARED_TYPE, 'logos/fictional-company-logo-' . $i . '.svg')],
                range(1, 8),
            );
        }

        $cells = '';
        foreach (array_slice($logos, 0, self::MAX_ITEMS['bookademo1']) as $index => $logo) {
            if (!is_array($logo)) {
                continue;
            }
            $url = self::imageUrlFromItem(
                (string) ($logo['url'] ?? ''),
                (int) $index,
                self::SHARED_TYPE,
                'logos/fictional-company-logo-' . (($index % 8) + 1) . '.svg',
            );
            $safeUrl = htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $cells .= '<div class="section-demo__logo-cell--' . $variant . '">'
                . '<img class="section-demo__logo-img--' . $variant . '" src="' . $safeUrl
                . '" alt="" width="96" height="96" loading="lazy" decoding="async" aria-hidden="true" />'
                . '</div>';
        }
        if ($cells === '') {
            return '';
        }

        return '<div class="section-demo__marquee--' . $variant . '" aria-hidden="true">'
            . '<div class="section-demo__marquee-track--' . $variant . '">'
            . $cells . $cells
            . '</div></div>';
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function formBookademo1Html(array $content): string
    {
        $submit = self::submitLabels($content, 'Envoyer');

        return '<div class="section-demo__form-card--bookademo1">'
            . '<form class="section-demo__form section-demo__form--bookademo1" data-contact-form novalidate'
            . ' toolname="demo_booking"'
            . ' tooldescription="Formulaire de demande de démo produit."'
            . ' action="#" method="post">'
            . self::formSuccess($submit['success'])
            . '<div class="section-demo__fields--bookademo1">'
            . '<div class="section-demo__field-row--bookademo1">'
            . self::inputField('demo1-first-name', 'first_name', 'Prénom', 'text', 'Alex', true)
            . self::inputField('demo1-last-name', 'last_name', 'Nom', 'text', 'Martin', true)
            . '</div>'
            . self::inputField('demo1-email', 'email', 'Email professionnel', 'email', 'alex.martin@entreprise.fr', true)
            . self::inputField('demo1-job', 'job_title', 'Poste', 'text', 'Développeur full stack', false)
            . self::textareaField('demo1-project', 'project', 'Que souhaitez-vous construire ?', 'Décrivez votre projet et votre stack technique', false)
            . self::selectField('demo1-source', 'source', 'Comment nous avez-vous découvert ?', [
                '' => 'GitHub, réseaux sociaux, etc.',
                'github' => 'GitHub',
                'social' => 'Réseaux sociaux',
                'blog' => 'Blog technique',
                'search' => 'Moteur de recherche',
                'conference' => 'Conférence',
                'referral' => 'Recommandation',
                'other' => 'Autre',
            ])
            . self::formError()
            . '<button type="submit" class="section-demo__submit--bookademo1" data-contact-submit>'
            . '<span data-contact-submit-label>' . $submit['label'] . '</span>'
            . '<span data-contact-submitting-label hidden>' . $submit['submitting'] . '</span>'
            . '</button>'
            . '</div></form></div>';
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function formBookademo2Html(array $content): string
    {
        $submit = self::submitLabels($content, 'Continuer');

        return '<div class="section-demo__form-panel--bookademo2">'
            . '<form class="section-demo__form section-demo__form--bookademo2" data-contact-form novalidate'
            . ' toolname="demo_booking"'
            . ' tooldescription="Formulaire de demande de démo produit."'
            . ' action="#" method="post">'
            . self::formSuccess($submit['success'])
            . '<div class="section-demo__fields--bookademo2">'
            . self::inputField('demo2-first-name', 'first_name', 'Prénom', 'text', 'Bruce', true, 'section-demo__field--half--bookademo2')
            . self::inputField('demo2-last-name', 'last_name', 'Nom', 'text', 'Wayne', true, 'section-demo__field--half--bookademo2')
            . self::inputField('demo2-email', 'email', 'Email', 'email', 'bruce@wayne.com', true, 'section-demo__field--full--bookademo2')
            . self::selectField('demo2-size', 'company_size', 'Taille de l\'entreprise', [
                '' => 'Sélectionnez une taille',
                '1-10' => '1-10',
                '11-50' => '11-50',
                '51-100' => '51-100',
                '101-500' => '101-500',
                '501-1000' => '501-1000',
            ], 'section-demo__field--half--bookademo2')
            . self::selectField('demo2-role', 'role', 'Rôle', [
                '' => 'Sélectionnez un rôle',
                'ceo' => 'PDG',
                'cto' => 'CTO',
                'cfo' => 'CFO',
                'other' => 'Autre',
            ], 'section-demo__field--half--bookademo2')
            . self::textareaField('demo2-message', 'message', 'Message', 'Partagez votre cas d\'usage, votre stack et vos objectifs', false, 'section-demo__field--full--bookademo2', 8)
            . self::formError()
            . '<button type="submit" class="section-demo__submit--bookademo2 section-demo__field--full--bookademo2" data-contact-submit>'
            . '<span data-contact-submit-label>' . $submit['label'] . ' <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>'
            . '<span data-contact-submitting-label hidden>' . $submit['submitting'] . '</span>'
            . '</button>'
            . '</div></form></div>';
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function headerBookademo2Html(array $content): string
    {
        $title = trim((string) ($content['title'] ?? ''));
        $text = trim((string) ($content['text'] ?? ''));
        $linkLabel = trim((string) ($content['link_label'] ?? ''));
        $href = self::hrefFromItem((string) ($content['href'] ?? '#'));
        $avatar1 = self::imageUrlFromItem((string) ($content['avatar1_url'] ?? ''), 0, self::SHARED_TYPE, 'portraits/avatar-2.jpg');
        $avatar2 = self::imageUrlFromItem((string) ($content['avatar2_url'] ?? ''), 1, self::SHARED_TYPE, 'portraits/avatar-1.jpg');

        $description = '';
        if ($text !== '') {
            $safeText = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            if ($linkLabel !== '' && str_contains($text, $linkLabel)) {
                $parts = explode($linkLabel, $text, 2);
                $safeLink = htmlspecialchars($linkLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $description = '<p class="section-demo__header-text--bookademo2">'
                    . htmlspecialchars($parts[0], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                    . '<a class="section-demo__header-link--bookademo2" href="' . $href . '">' . $safeLink . '</a>'
                    . htmlspecialchars($parts[1] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                    . '</p>';
            } else {
                $description = '<p class="section-demo__header-text--bookademo2">' . $safeText . '</p>';
            }
        }

        return '<div class="section-demo__header--bookademo2">'
            . ($title !== '' ? '<h2 class="section-demo__header-title--bookademo2">'
                . htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</h2>' : '')
            . $description
            . '<div class="section-demo__header-avatars--bookademo2" aria-hidden="true">'
            . self::floatingAvatarHtml($avatar1, 'section-demo__avatar--left--bookademo2', 'section-demo__avatar-ring--orange--bookademo2')
            . self::floatingAvatarHtml($avatar2, 'section-demo__avatar--right--bookademo2', 'section-demo__avatar-ring--blue--bookademo2', true)
            . '</div></div>';
    }

    private static function floatingAvatarHtml(string $url, string $positionClass, string $ringClass, bool $delayed = false): string
    {
        $safeUrl = htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $delayClass = $delayed ? ' section-demo__avatar--delayed--bookademo2' : '';

        return '<div class="section-demo__avatar-wrap--bookademo2 ' . $positionClass . $delayClass . '">'
            . '<i class="fa-solid fa-arrow-pointer section-demo__avatar-cursor--bookademo2" aria-hidden="true"></i>'
            . '<div class="section-demo__avatar-ring--bookademo2 ' . $ringClass . '">'
            . '<img class="section-demo__avatar-img--bookademo2" src="' . $safeUrl
            . '" alt="" width="40" height="40" loading="lazy" decoding="async" />'
            . '</div></div>';
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function testimonialsBookademo2Html(array $content): string
    {
        $items = self::itemsFromContent($content, self::MAX_ITEMS['bookademo2']);
        if ($items === []) {
            return '';
        }

        $slides = '';
        foreach ($items as $index => $item) {
            $quote = trim((string) ($item['text'] ?? ''));
            if ($quote === '') {
                continue;
            }
            $highlights = self::highlightWords((string) ($item['label'] ?? ''));
            $author = trim((string) ($item['author'] ?? ''));
            $role = trim((string) ($item['role'] ?? ''));
            $logo = self::imageUrlFromItem(
                (string) ($item['logo'] ?? ''),
                (int) $index,
                self::SHARED_TYPE,
                'logos/fictional-company-logo-' . (($index % 6) + 1) . '.svg',
            );
            $avatar = self::imageUrlFromItem(
                (string) ($item['url'] ?? ''),
                (int) $index,
                self::SHARED_TYPE,
                'portraits/avatar-' . (($index % 6) + 1) . '.jpg',
            );
            $active = $index === 0 ? ' is-active' : '';
            $slides .= '<article class="section-demo__testimonial--bookademo2' . $active . '" data-demo-testimonial data-index="' . $index . '">'
                . '<img class="section-demo__testimonial-logo--bookademo2" src="'
                . htmlspecialchars($logo, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                . '" alt="" width="120" height="32" loading="lazy" decoding="async" aria-hidden="true" />'
                . '<blockquote class="section-demo__testimonial-quote--bookademo2"><p>'
                . self::highlightedQuoteHtml($quote, $highlights)
                . '</p></blockquote>'
                . '<div class="section-demo__testimonial-author--bookademo2">'
                . '<img class="section-demo__testimonial-avatar--bookademo2" src="'
                . htmlspecialchars($avatar, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                . '" alt="" width="36" height="36" loading="lazy" decoding="async" />'
                . '<div><p class="section-demo__testimonial-name--bookademo2">'
                . htmlspecialchars($author, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                . '</p><p class="section-demo__testimonial-role--bookademo2">'
                . htmlspecialchars($role, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                . '</p></div></div></article>';
        }

        return '<div class="section-demo__testimonials--bookademo2" data-demo-testimonials data-demo-testimonial-count="' . count($items) . '">'
            . '<div class="section-demo__testimonials-nav--bookademo2">'
            . '<button type="button" class="section-demo__testimonials-btn--bookademo2" data-demo-testimonial-prev aria-label="Témoignage précédent">'
            . '<i class="fa-solid fa-arrow-left" aria-hidden="true"></i></button>'
            . '<button type="button" class="section-demo__testimonials-btn--bookademo2" data-demo-testimonial-next aria-label="Témoignage suivant">'
            . '<i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>'
            . '</div>'
            . '<div class="section-demo__testimonials-track--bookademo2">' . $slides . '</div>'
            . '</div>';
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function footerBookademo2Html(array $content): string
    {
        $heading = trim((string) ($content['footer_title'] ?? ''));
        $logosHtml = self::footerLogosHtml($content);

        return '<div class="section-demo__footer--bookademo2">'
            . ($heading !== '' ? '<h3 class="section-demo__footer-title--bookademo2">'
                . htmlspecialchars($heading, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</h3>' : '')
            . $logosHtml
            . '</div>';
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function footerLogosHtml(array $content): string
    {
        $logos = is_array($content['logos'] ?? null) ? $content['logos'] : [];
        if ($logos === []) {
            return '';
        }

        $html = '<div class="section-demo__footer-logos--bookademo2">';
        foreach (array_slice($logos, 0, 10) as $index => $logo) {
            if (!is_array($logo)) {
                continue;
            }
            $url = self::imageUrlFromItem(
                (string) ($logo['url'] ?? ''),
                (int) $index,
                self::SHARED_TYPE,
                'logos/fictional-company-logo-' . (($index % 10) + 1) . '.svg',
            );
            $hidden = $index >= 6 ? ' section-demo__footer-logo--hidden-mobile--bookademo2' : '';
            $html .= '<img class="section-demo__footer-logo--bookademo2' . $hidden . '" src="'
                . htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                . '" alt="" width="120" height="32" loading="lazy" decoding="async" aria-hidden="true" />';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * @return list<string>
     */
    private static function highlightWords(string $raw): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\s*,\s*/', $raw) ?: [])));
    }

    /**
     * @param list<string> $highlights
     */
    private static function highlightedQuoteHtml(string $quote, array $highlights): string
    {
        $html = '';
        foreach (preg_split('/\s+/', $quote) ?: [] as $word) {
            $plain = trim($word);
            if ($plain === '') {
                continue;
            }
            $isHighlight = false;
            foreach ($highlights as $highlight) {
                if ($highlight !== '' && stripos($plain, $highlight) !== false) {
                    $isHighlight = true;
                    break;
                }
            }
            $safe = htmlspecialchars($plain, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $html .= $isHighlight
                ? '<span class="section-demo__quote-highlight--bookademo2">' . $safe . '</span> '
                : $safe . ' ';
        }

        return rtrim($html);
    }

    /**
     * @param array<string, mixed> $content
     *
     * @return array{success: string, label: string, submitting: string}
     */
    private static function submitLabels(array $content, string $defaultLabel): array
    {
        return [
            'success' => htmlspecialchars(
                (string) ($content['success_message'] ?? 'Merci, votre demande a bien été enregistrée.'),
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8',
            ),
            'label' => htmlspecialchars(
                (string) ($content['submit_label'] ?? $defaultLabel),
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8',
            ),
            'submitting' => htmlspecialchars(
                (string) ($content['submitting_label'] ?? 'Envoi en cours…'),
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8',
            ),
        ];
    }

    private static function formSuccess(string $message): string
    {
        return '<div class="section-demo__form-success section-contact__form-success" role="status" aria-live="polite" hidden>'
            . '<p class="section-demo__form-success-text section-contact__form-success-text">' . $message . '</p>'
            . '</div>';
    }

    private static function formError(): string
    {
        return '<p class="section-demo__form-error section-contact__form-error" role="alert" hidden></p>';
    }

    /**
     * @param array<string, string> $options
     */
    private static function selectField(
        string $id,
        string $name,
        string $label,
        array $options,
        string $modifierClass = '',
    ): string {
        $safeId = htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeName = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $class = 'section-demo__field' . ($modifierClass !== '' ? ' ' . $modifierClass : '');
        $html = '<div class="' . $class . '">'
            . '<label class="section-demo__label" for="' . $safeId . '">' . $safeLabel . '</label>'
            . '<select class="section-demo__select" id="' . $safeId . '" name="' . $safeName
            . '" toolparamdescription="' . $safeLabel . '">';
        foreach ($options as $value => $optionLabel) {
            $html .= '<option value="' . htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">'
                . htmlspecialchars($optionLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</option>';
        }
        $html .= '</select></div>';

        return $html;
    }

    private static function inputField(
        string $id,
        string $name,
        string $label,
        string $type,
        string $placeholder,
        bool $required,
        string $modifierClass = '',
    ): string {
        $safeId = htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeName = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safePlaceholder = htmlspecialchars($placeholder, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $req = $required ? ' required' : '';
        $class = 'section-demo__field' . ($modifierClass !== '' ? ' ' . $modifierClass : '');

        return '<div class="' . $class . '">'
            . '<label class="section-demo__label" for="' . $safeId . '">' . $safeLabel . '</label>'
            . '<input class="section-demo__input" id="' . $safeId . '" name="' . $safeName . '" type="' . $type . '"'
            . ' placeholder="' . $safePlaceholder . '" toolparamdescription="' . $safeLabel . '"' . $req . ' />'
            . '</div>';
    }

    private static function textareaField(
        string $id,
        string $name,
        string $label,
        string $placeholder,
        bool $required,
        string $modifierClass = '',
        int $rows = 4,
    ): string {
        $safeId = htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeName = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safePlaceholder = htmlspecialchars($placeholder, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $req = $required ? ' required' : '';
        $class = 'section-demo__field' . ($modifierClass !== '' ? ' ' . $modifierClass : '');

        return '<div class="' . $class . '">'
            . '<label class="section-demo__label" for="' . $safeId . '">' . $safeLabel . '</label>'
            . '<textarea class="section-demo__textarea" id="' . $safeId . '" name="' . $safeName . '" rows="' . $rows . '"'
            . ' placeholder="' . $safePlaceholder . '" toolparamdescription="' . $safeLabel . '"' . $req . '></textarea>'
            . '</div>';
    }
}
