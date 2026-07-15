<?php

declare(strict_types=1);

namespace Capsule\Section\Contact;

/**
 * Rendu HTML spécifique aux variantes contact (conversion des blocs React).
 */
final class ContactVariantRenderer
{
    /** @var array<string, string> */
    private const ICON_CLASSES = [
        'mail' => 'fa-solid fa-envelope',
        'map' => 'fa-solid fa-location-dot',
        'phone' => 'fa-solid fa-phone',
        'chat' => 'fa-solid fa-comment',
    ];

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    public static function enrich(array $data, array $content, string $variant): array
    {
        $data['contact_links_html'] = '';
        $data['contact_form_html'] = '';
        $data['contact_cards_html'] = '';

        return match ($variant) {
            'contact2' => self::enrichContact2($data, $content),
            'contact7' => self::enrichContact7($data, $content),
            default => $data,
        };
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichContact2(array $data, array $content): array
    {
        $phone = trim((string) ($content['phone'] ?? ''));
        $email = trim((string) ($content['email'] ?? ''));
        $webLabel = trim((string) ($content['web_label'] ?? ''));
        $webUrl = trim((string) ($content['web_url'] ?? ''));

        $links = '';
        if ($phone !== '') {
            $safePhone = htmlspecialchars($phone, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $tel = htmlspecialchars(self::telHref($phone), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $links .= '<a class="section-contact__link" href="tel:' . $tel . '">'
                . '<i class="section-contact__link-icon fa-solid fa-phone" aria-hidden="true"></i>'
                . '<span>' . $safePhone . '</span></a>';
        }
        if ($email !== '') {
            $safeEmail = htmlspecialchars($email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $links .= '<a class="section-contact__link" href="mailto:' . $safeEmail . '">'
                . '<i class="section-contact__link-icon fa-solid fa-envelope" aria-hidden="true"></i>'
                . '<span>' . $safeEmail . '</span></a>';
        }
        if ($webLabel !== '' && $webUrl !== '') {
            $safeLabel = htmlspecialchars($webLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeUrl = htmlspecialchars($webUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $links .= '<a class="section-contact__link" href="' . $safeUrl . '" target="_blank" rel="noopener noreferrer"'
                . ' aria-label="' . $safeLabel . ' (ouvre un nouvel onglet)">'
                . '<i class="section-contact__link-icon fa-solid fa-globe" aria-hidden="true"></i>'
                . '<span>' . $safeLabel . '</span></a>';
        }
        $data['contact_links_html'] = $links;
        $data['contact_form_html'] = self::renderContact2Form($content);

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichContact7(array $data, array $content): array
    {
        $cards = [
            [
                'icon' => 'mail',
                'label' => (string) ($content['email_label'] ?? 'Email'),
                'description' => (string) ($content['email_description'] ?? ''),
                'value' => (string) ($content['email'] ?? ''),
                'href' => 'mailto:' . trim((string) ($content['email'] ?? '')),
            ],
            [
                'icon' => 'map',
                'label' => (string) ($content['office_label'] ?? 'Bureau'),
                'description' => (string) ($content['office_description'] ?? ''),
                'value' => (string) ($content['office_address'] ?? ''),
                'href' => trim((string) ($content['office_href'] ?? '#')),
            ],
            [
                'icon' => 'phone',
                'label' => (string) ($content['phone_label'] ?? 'Téléphone'),
                'description' => (string) ($content['phone_description'] ?? ''),
                'value' => (string) ($content['phone'] ?? ''),
                'href' => 'tel:' . self::telHref((string) ($content['phone'] ?? '')),
            ],
            [
                'icon' => 'chat',
                'label' => (string) ($content['chat_label'] ?? 'Chat en direct'),
                'description' => (string) ($content['chat_description'] ?? ''),
                'value' => (string) ($content['chat_link'] ?? ''),
                'href' => trim((string) ($content['chat_href'] ?? '#')),
            ],
        ];

        $html = '';
        foreach ($cards as $card) {
            $html .= self::renderContact7Card($card);
        }
        $data['contact_cards_html'] = $html;

        return $data;
    }

    /**
     * @param array<string, mixed> $content
     */
    private static function renderContact2Form(array $content): string
    {
        $successMessage = htmlspecialchars(
            (string) ($content['success_message'] ?? 'Merci, votre message est bien arrivé.'),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8',
        );
        $submitLabel = htmlspecialchars(
            (string) ($content['submit_label'] ?? 'Envoyer le message'),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8',
        );
        $submittingLabel = htmlspecialchars(
            (string) ($content['submitting_label'] ?? 'Envoi en cours…'),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8',
        );

        return '<form class="section-contact__form" data-contact-form novalidate'
            . ' toolname="contact_message"'
            . ' tooldescription="Formulaire de contact pour envoyer un message à l\'équipe."'
            . ' action="#" method="post">'
            . '<div class="section-contact__form-success" role="status" aria-live="polite" hidden>'
            . '<p class="section-contact__form-success-text">' . $successMessage . '</p>'
            . '</div>'
            . '<div class="section-contact__fields">'
            . '<div class="section-contact__field-row">'
            . self::formField('contact-first-name', 'first_name', 'Prénom', 'text', 'Jordan', true)
            . self::formField('contact-last-name', 'last_name', 'Nom', 'text', 'Martin', true)
            . '</div>'
            . self::formField('contact-email', 'email', 'Email', 'email', 'vous@entreprise.fr', true)
            . self::formField('contact-subject', 'subject', 'Sujet', 'text', 'Question sur les blocs', true)
            . self::formTextarea('contact-message', 'message', 'Message', 'Décrivez votre projet…', true)
            . '<p class="section-contact__form-error" role="alert" hidden></p>'
            . '<button type="submit" class="section-contact__btn" data-contact-submit>'
            . '<span data-contact-submit-label>' . $submitLabel . '</span>'
            . '<span data-contact-submitting-label hidden>' . $submittingLabel . '</span>'
            . '</button>'
            . '</div>'
            . '</form>';
    }

    private static function formField(
        string $id,
        string $name,
        string $label,
        string $type,
        string $placeholder,
        bool $required,
    ): string {
        $safeId = htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeName = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safePlaceholder = htmlspecialchars($placeholder, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $req = $required ? ' required' : '';
        $star = $required ? ' <span class="section-contact__required" aria-hidden="true">*</span>' : '';

        return '<div class="section-contact__field">'
            . '<label class="section-contact__label" for="' . $safeId . '">' . $safeLabel . $star . '</label>'
            . '<input class="section-contact__input" id="' . $safeId . '" name="' . $safeName . '" type="' . $type . '"'
            . ' placeholder="' . $safePlaceholder . '" toolparamdescription="' . $safeLabel . '"' . $req . ' />'
            . '</div>';
    }

    private static function formTextarea(
        string $id,
        string $name,
        string $label,
        string $placeholder,
        bool $required,
    ): string {
        $safeId = htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeName = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safePlaceholder = htmlspecialchars($placeholder, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $req = $required ? ' required' : '';
        $star = $required ? ' <span class="section-contact__required" aria-hidden="true">*</span>' : '';

        return '<div class="section-contact__field">'
            . '<label class="section-contact__label" for="' . $safeId . '">' . $safeLabel . $star . '</label>'
            . '<textarea class="section-contact__textarea" id="' . $safeId . '" name="' . $safeName . '" rows="4"'
            . ' placeholder="' . $safePlaceholder . '" toolparamdescription="' . $safeLabel . '"' . $req . '></textarea>'
            . '</div>';
    }

    /**
     * @param array{icon: string, label: string, description: string, value: string, href: string} $card
     */
    private static function renderContact7Card(array $card): string
    {
        $icon = self::ICON_CLASSES[$card['icon']] ?? self::ICON_CLASSES['mail'];
        $safeLabel = htmlspecialchars($card['label'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeDescription = htmlspecialchars($card['description'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeValue = htmlspecialchars($card['value'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeHref = htmlspecialchars($card['href'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<article class="section-contact__card">'
            . '<i class="section-contact__card-icon ' . $icon . '" aria-hidden="true"></i>'
            . '<p class="section-contact__card-label">' . $safeLabel . '</p>'
            . '<p class="section-contact__card-description">' . $safeDescription . '</p>'
            . '<a class="section-contact__card-link" href="' . $safeHref . '">' . $safeValue . '</a>'
            . '</article>';
    }

    private static function telHref(string $phone): string
    {
        return preg_replace('/[^\d+]/', '', $phone) ?? '';
    }
}
