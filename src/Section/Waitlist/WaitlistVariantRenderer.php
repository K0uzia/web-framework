<?php

declare(strict_types=1);

namespace Capsule\Section\Waitlist;

use Capsule\SectionAssets;
use Capsule\Section\SectionItemsTrait;
use Capsule\WaitlistBackgroundLines;

/**
 * Rendu HTML spécifique aux variantes waitlist (inscription liste d'attente shadcnblocks).
 */
final class WaitlistVariantRenderer
{
    use SectionItemsTrait;

    private const SHARED_TYPE = 'waitlist';

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    public static function enrich(array $data, array $content, string $variant): array
    {
        $data['waitlist_background_html'] = WaitlistBackgroundLines::svgHtml();
        $data['waitlist_form_html'] = self::formHtml($content);
        $data['waitlist_avatars_html'] = self::avatarsHtml($content);

        return $data;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function formHtml(array $content): string
    {
        $submit = self::submitLabels($content, 'Rejoindre la liste');
        $placeholder = htmlspecialchars(
            (string) ($content['placeholder'] ?? 'Votre adresse email'),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8',
        );

        return '<form class="section-waitlist__form section-waitlist__form--waitlist1" data-contact-form novalidate'
            . ' toolname="waitlist_signup"'
            . ' tooldescription="Inscription à la liste d\'attente par email."'
            . ' action="#" method="post">'
            . self::formSuccess($submit['success'])
            . '<div class="section-waitlist__form-shell--waitlist1">'
            . '<label class="section-waitlist__sr-only--waitlist1" for="waitlist1-email">Email</label>'
            . '<input class="section-waitlist__input--waitlist1" id="waitlist1-email" name="email" type="email"'
            . ' placeholder="' . $placeholder . '" required autocomplete="email"'
            . ' toolparamdescription="Adresse email pour la liste d\'attente" />'
            . self::formError()
            . '<button type="submit" class="section-waitlist__submit--waitlist1" data-contact-submit>'
            . '<span data-contact-submit-label>' . $submit['label'] . '</span>'
            . '<span data-contact-submitting-label hidden>' . $submit['submitting'] . '</span>'
            . '</button>'
            . '</div></form>';
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function avatarsHtml(array $content): string
    {
        $items = self::itemsFromContent($content, 8);
        if ($items === []) {
            $items = array_map(
                static fn (int $i): array => [
                    'url' => SectionAssets::shared(self::SHARED_TYPE, 'avatars/avatar-' . $i . '.png'),
                ],
                range(1, 6),
            );
        }

        $html = '<span class="section-waitlist__avatars--waitlist1" aria-hidden="true">';
        foreach ($items as $index => $item) {
            $url = self::imageUrlFromItem(
                (string) ($item['url'] ?? ''),
                (int) $index,
                self::SHARED_TYPE,
                'avatars/avatar-' . (($index % 6) + 1) . '.png',
            );
            $html .= '<img class="section-waitlist__avatar--waitlist1" src="'
                . htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                . '" alt="" width="32" height="32" loading="lazy" decoding="async" />';
        }
        $html .= '</span>';

        return $html;
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
                (string) ($content['success_message'] ?? 'Merci, vous êtes bien inscrit(e) à la liste d\'attente.'),
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8',
            ),
            'label' => htmlspecialchars(
                (string) ($content['submit_label'] ?? $defaultLabel),
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8',
            ),
            'submitting' => htmlspecialchars(
                (string) ($content['submitting_label'] ?? 'Inscription en cours…'),
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8',
            ),
        ];
    }

    private static function formSuccess(string $message): string
    {
        return '<div class="section-waitlist__form-success section-contact__form-success" role="status" aria-live="polite" hidden>'
            . '<p class="section-waitlist__form-success-text section-contact__form-success-text">' . $message . '</p>'
            . '</div>';
    }

    private static function formError(): string
    {
        return '<p class="section-waitlist__form-error section-contact__form-error" role="alert" hidden></p>';
    }
}
