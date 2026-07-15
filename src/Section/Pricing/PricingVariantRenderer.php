<?php

declare(strict_types=1);

namespace Capsule\Section\Pricing;

/**
 * Rendu HTML spécifique aux variantes pricing (conversion des blocs React).
 */
final class PricingVariantRenderer
{
    private const SHARED = 'pricing';

    /** @var array<string, int> */
    private const MAX_PLANS = [
        'pricing2' => 4,
        'pricing4' => 3,
        'pricing6' => 1,
        'pricing11' => 4,
    ];

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    public static function enrich(array $data, array $content, string $variant): array
    {
        $data['pricing_plans_html'] = '';
        $data['pricing_billing_html'] = '';
        $data['pricing_matrix_html'] = '';
        $data['pricing_single_html'] = '';

        return match ($variant) {
            'pricing2' => self::enrichPricing2($data, $content),
            'pricing4' => self::enrichPricing4($data, $content),
            'pricing6' => self::enrichPricing6($data, $content),
            'pricing11' => self::enrichPricing11($data, $content),
            default => $data,
        };
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichPricing2(array $data, array $content): array
    {
        $data['pricing_billing_html'] = self::renderBillingSwitch(
            (string) ($content['billing_monthly_label'] ?? 'Mensuel'),
            (string) ($content['billing_yearly_label'] ?? 'Annuel'),
        );

        $html = '';
        foreach (self::plans($content, 'pricing2') as $item) {
            $html .= self::renderPricing2Card($item);
        }
        $data['pricing_plans_html'] = $html;

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichPricing4(array $data, array $content): array
    {
        $data['pricing_billing_html'] = self::renderBillingTabs(
            (string) ($content['billing_monthly_label'] ?? 'Mensuel'),
            (string) ($content['billing_yearly_label'] ?? 'Annuel'),
        );

        $html = '';
        $highlightIndex = -1;
        $plans = self::plans($content, 'pricing4');
        foreach ($plans as $index => $item) {
            if ($highlightIndex === -1 && self::isHighlighted($item)) {
                $highlightIndex = $index;
            }
        }
        foreach ($plans as $index => $item) {
            $html .= self::renderPricing4Card($item, $highlightIndex !== -1 && $index === $highlightIndex);
        }
        $data['pricing_plans_html'] = $html;

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichPricing6(array $data, array $content): array
    {
        $priceMonthly = trim((string) ($content['price_monthly'] ?? '49'));
        $priceMonthly = ltrim($priceMonthly, '$€');
        $period = trim((string) ($content['period_monthly'] ?? '/mois'));
        $buttonLabel = trim((string) ($content['button_label'] ?? 'Commencer'));
        $buttonHref = trim((string) ($content['button_href'] ?? '#'));
        $groupsHtml = '';
        $groups = self::featureGroups($content);
        $groupCount = count($groups);
        foreach ($groups as $index => $features) {
            $groupsHtml .= '<div class="section-pricing__feature-group"><ul class="section-pricing__feature-list section-pricing__feature-list--split">';
            foreach ($features as $feature) {
                $safe = htmlspecialchars($feature, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $groupsHtml .= '<li><span>' . $safe . '</span><i class="fa-solid fa-check" aria-hidden="true"></i></li>';
            }
            $groupsHtml .= '</ul>';
            if ($index < $groupCount - 1) {
                $groupsHtml .= '<hr class="section-pricing__separator" />';
            }
            $groupsHtml .= '</div>';
        }

        $data['pricing_single_html'] = '<div class="section-pricing__single-card">'
            . '<div class="section-pricing__single-price">'
            . '<span class="section-pricing__single-currency">$</span>'
            . '<span class="section-pricing__single-amount">' . htmlspecialchars($priceMonthly, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span>'
            . '<span class="section-pricing__single-period">' . htmlspecialchars($period, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span>'
            . '</div>'
            . '<div class="section-pricing__single-features">' . $groupsHtml . '</div>'
            . self::renderButton($buttonLabel, $buttonHref, true)
            . '</div>';

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private static function enrichPricing11(array $data, array $content): array
    {
        $plans = self::plans($content, 'pricing11');
        $planTitles = array_map(static fn (array $item): string => trim((string) ($item['title'] ?? '')), $plans);
        $data['pricing_billing_html'] = self::renderBillingTabsCompact(
            (string) ($content['billing_monthly_label'] ?? 'Mensuel'),
            (string) ($content['billing_yearly_label'] ?? 'Annuel'),
        );

        $headerHtml = '';
        foreach ($plans as $item) {
            $headerHtml .= self::renderPricing11PlanHeader($item);
        }
        $data['pricing_plans_html'] = $headerHtml;
        $data['pricing_matrix_html'] = self::renderPricing11Matrix($planTitles);

        return $data;
    }

    private static function renderBillingSwitch(string $monthly, string $yearly): string
    {
        $safeMonthly = htmlspecialchars($monthly, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeYearly = htmlspecialchars($yearly, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<div class="section-pricing__billing section-pricing__billing--switch">'
            . '<span>' . $safeMonthly . '</span>'
            . '<label class="section-pricing__switch">'
            . '<input type="checkbox" class="section-pricing__switch-input" data-pricing-billing-switch aria-label="'
            . htmlspecialchars($monthly . ' / ' . $yearly, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" />'
            . '<span class="section-pricing__switch-track" aria-hidden="true"></span>'
            . '</label>'
            . '<span>' . $safeYearly . '</span>'
            . '</div>';
    }

    private static function renderBillingTabs(string $monthly, string $yearly): string
    {
        $safeMonthly = htmlspecialchars($monthly, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeYearly = htmlspecialchars($yearly, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<div class="section-pricing__billing section-pricing__billing--tabs" role="tablist" aria-label="Période de facturation">'
            . '<button type="button" class="section-pricing__billing-tab is-active" data-pricing-billing-tab="monthly" role="tab" aria-selected="true">'
            . $safeMonthly . '</button>'
            . '<button type="button" class="section-pricing__billing-tab" data-pricing-billing-tab="yearly" role="tab" aria-selected="false">'
            . $safeYearly . '</button>'
            . '</div>';
    }

    private static function renderBillingTabsCompact(string $monthly, string $yearly): string
    {
        $safeMonthly = htmlspecialchars($monthly, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeYearly = htmlspecialchars($yearly, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<div class="section-pricing__billing section-pricing__billing--tabs section-pricing__billing--compact" role="tablist" aria-label="Période de facturation">'
            . '<button type="button" class="section-pricing__billing-tab is-active" data-pricing-billing-tab="monthly" role="tab" aria-selected="true">'
            . $safeMonthly . '</button>'
            . '<button type="button" class="section-pricing__billing-tab" data-pricing-billing-tab="yearly" role="tab" aria-selected="false">'
            . $safeYearly . '</button>'
            . '</div>';
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function renderPricing2Card(array $item): string
    {
        $title = htmlspecialchars(trim((string) ($item['title'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $text = htmlspecialchars(trim((string) ($item['text'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $monthly = htmlspecialchars(self::formatPrice((string) ($item['price_monthly'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $yearly = htmlspecialchars(self::formatPrice((string) ($item['price_yearly'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $buttonLabel = htmlspecialchars(trim((string) ($item['label'] ?? 'Choisir')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $href = htmlspecialchars(trim((string) ($item['href'] ?? '#')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $highlighted = self::isHighlighted($item);
        $cardClass = 'section-pricing__card section-pricing__card--plan2' . ($highlighted ? ' section-pricing__card--highlighted' : '');
        $features = self::parseFeatures((string) ($item['features'] ?? ''));
        $featuresHtml = '';
        foreach ($features as $feature) {
            $safe = htmlspecialchars($feature, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $featuresHtml .= '<li><i class="fa-solid fa-circle-check" aria-hidden="true"></i><span>' . $safe . '</span></li>';
        }

        return '<article class="' . $cardClass . '">'
            . '<header class="section-pricing__card-head">'
            . '<h3 class="section-pricing__card-name">' . $title . '</h3>'
            . '<p class="section-pricing__card-price">'
            . '<span class="section-pricing__price-value" data-price-monthly>' . $monthly . '</span>'
            . '<span class="section-pricing__price-value" data-price-yearly hidden>' . $yearly . '</span>'
            . '<span class="section-pricing__price-period" data-price-monthly>/par mois</span>'
            . '<span class="section-pricing__price-period" data-price-yearly hidden>/par an</span>'
            . '</p>'
            . '<p class="section-pricing__card-desc">' . $text . '</p>'
            . '</header>'
            . '<div class="section-pricing__card-body"><hr class="section-pricing__separator" />'
            . '<ul class="section-pricing__feature-list">' . $featuresHtml . '</ul></div>'
            . '<footer class="section-pricing__card-foot">' . self::renderButton($buttonLabel, $href, $highlighted) . '</footer>'
            . '</article>';
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function renderPricing4Card(array $item, bool $highlighted): string
    {
        $title = trim((string) ($item['title'] ?? ''));
        $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $monthly = htmlspecialchars(self::formatPrice((string) ($item['price_monthly'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $yearly = htmlspecialchars(self::formatPrice((string) ($item['price_yearly'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $periodMonthly = htmlspecialchars(trim((string) ($item['period_monthly'] ?? 'Par mois')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $periodYearly = htmlspecialchars(trim((string) ($item['period_yearly'] ?? 'Par an')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $buttonLabel = htmlspecialchars(trim((string) ($item['label'] ?? 'Choisir')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $href = htmlspecialchars(trim((string) ($item['href'] ?? '#')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $badgeVariant = $highlighted ? ' section-pricing__badge--solid' : ' section-pricing__badge--outline';
        $cardClass = 'section-pricing__card section-pricing__card--plan4' . ($highlighted ? ' section-pricing__card--muted' : '');
        $features = self::parseFeatures((string) ($item['features'] ?? ''));
        $featuresHtml = '';
        foreach ($features as $feature) {
            $safe = htmlspecialchars($feature, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $featuresHtml .= '<li><i class="fa-solid fa-check" aria-hidden="true"></i><span>' . $safe . '</span></li>';
        }
        $hidePeriod = self::formatPrice((string) ($item['price_monthly'] ?? '')) === '$0' ? ' section-pricing__card-period--hidden' : '';

        return '<article class="' . $cardClass . '">'
            . '<span class="section-pricing__badge' . $badgeVariant . '">' . $safeTitle . '</span>'
            . '<p class="section-pricing__card-price section-pricing__card-price--lg">'
            . '<span data-price-monthly>' . $monthly . '</span>'
            . '<span data-price-yearly hidden>' . $yearly . '</span>'
            . '</p>'
            . '<p class="section-pricing__card-period' . $hidePeriod . '">'
            . '<span data-price-monthly>' . $periodMonthly . '</span>'
            . '<span data-price-yearly hidden>' . $periodYearly . '</span>'
            . '</p>'
            . '<hr class="section-pricing__separator" />'
            . '<div class="section-pricing__card-body section-pricing__card-body--grow">'
            . '<ul class="section-pricing__feature-list section-pricing__feature-list--muted">' . $featuresHtml . '</ul>'
            . self::renderButton($buttonLabel, $href, $highlighted)
            . '</div>'
            . '</article>';
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function renderPricing11PlanHeader(array $item): string
    {
        $title = htmlspecialchars(trim((string) ($item['title'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $monthly = htmlspecialchars(self::formatPrice((string) ($item['price_monthly'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $yearly = htmlspecialchars(self::formatPrice((string) ($item['price_yearly'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $href = htmlspecialchars(trim((string) ($item['href'] ?? '#')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $highlighted = self::isHighlighted($item);

        return '<div class="section-pricing__matrix-plan">'
            . '<h3 class="section-pricing__matrix-plan-title">' . $title . '</h3>'
            . '<p class="section-pricing__matrix-plan-price">'
            . '<span data-price-monthly>' . $monthly . '</span>'
            . '<span data-price-yearly hidden>' . $yearly . '</span>'
            . '<span class="section-pricing__matrix-plan-period"> / mois</span>'
            . '</p>'
            . self::renderButton('S\'inscrire', $href, $highlighted, 'section-pricing__btn--sm')
            . '</div>';
    }

    /**
     * @param list<string> $planTitles
     */
    private static function renderPricing11Matrix(array $planTitles): string
    {
        $matrix = self::pricing11Matrix();
        $html = '';
        foreach ($matrix as $category) {
            $html .= '<div class="section-pricing__matrix-category">'
                . '<h3 class="section-pricing__matrix-category-title">' . htmlspecialchars($category['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</h3>'
                . '<div class="section-pricing__matrix-rows">';
            foreach ($category['features'] as $feature) {
                $html .= self::renderPricing11FeatureRow($feature, $planTitles);
            }
            $html .= '</div></div>';
        }

        return $html . '<p class="section-pricing__matrix-footnote">* Conditions et réserves applicables</p>';
    }

    /**
     * @param array{title: string, info?: string, cells: array<string, string>} $feature
     * @param list<string> $planTitles
     */
    private static function renderPricing11FeatureRow(array $feature, array $planTitles): string
    {
        $title = htmlspecialchars($feature['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $info = trim((string) ($feature['info'] ?? ''));
        $infoHtml = $info !== ''
            ? ' <span class="section-pricing__matrix-info" title="' . htmlspecialchars($info, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"><i class="fa-solid fa-circle-info" aria-hidden="true"></i><span class="visually-hidden">' . htmlspecialchars($info, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span></span>'
            : '';

        $desktopCells = '';
        $mobileCells = '';
        foreach ($planTitles as $planTitle) {
            $cell = $feature['cells'][$planTitle] ?? '';
            $desktopCells .= '<dd class="section-pricing__matrix-cell">' . self::renderMatrixCell($cell) . '</dd>';
            $safePlan = htmlspecialchars($planTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $mobileCells .= '<dd class="section-pricing__matrix-mobile-cell"><span class="section-pricing__matrix-mobile-plan">' . $safePlan . '</span>' . self::renderMatrixCell($cell) . '</dd>';
        }

        return '<div class="section-pricing__matrix-row" data-pricing-matrix-row>'
            . '<dl class="section-pricing__matrix-desktop">'
            . '<dt class="section-pricing__matrix-label"><span>' . $title . $infoHtml . '</span></dt>'
            . $desktopCells
            . '</dl>'
            . '<details class="section-pricing__matrix-mobile">'
            . '<summary class="section-pricing__matrix-mobile-summary"><span>' . $title . $infoHtml . '</span><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></summary>'
            . '<dl class="section-pricing__matrix-mobile-list">' . $mobileCells . '</dl>'
            . '</details>'
            . '</div>';
    }

    private static function renderMatrixCell(string $cell): string
    {
        return match ($cell) {
            'check' => '<i class="fa-solid fa-check section-pricing__matrix-icon" aria-hidden="true"></i><span class="visually-hidden">Inclus</span>',
            'x' => '<i class="fa-solid fa-xmark section-pricing__matrix-icon section-pricing__matrix-icon--muted" aria-hidden="true"></i><span class="visually-hidden">Non inclus</span>',
            'addon' => '<span class="section-pricing__matrix-badge">Option</span>',
            default => htmlspecialchars($cell, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
        };
    }

    private static function renderButton(string $label, string $href, bool $primary, string $extraClass = ''): string
    {
        if ($label === '') {
            return '';
        }
        $variant = $primary ? 'primary' : 'outline';
        $class = 'section-pricing__btn section-pricing__btn--' . $variant . ($extraClass !== '' ? ' ' . $extraClass : '');

        return '<a class="' . $class . '" href="' . htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">'
            . htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</a>';
    }

    /**
     * @param array<string, mixed> $content
     *
     * @return list<array<string, mixed>>
     */
    private static function plans(array $content, string $variant): array
    {
        $raw = is_array($content['items'] ?? null) ? $content['items'] : [];
        $max = self::MAX_PLANS[$variant] ?? 3;
        $plans = [];
        foreach (array_slice($raw, 0, $max) as $item) {
            if (is_array($item)) {
                $plans[] = $item;
            }
        }

        return $plans;
    }

    /**
     * @param array<string, mixed> $content
     *
     * @return list<list<string>>
     */
    private static function featureGroups(array $content): array
    {
        $raw = is_array($content['items'] ?? null) ? $content['items'] : [];
        $groups = [];
        foreach ($raw as $item) {
            if (!is_array($item)) {
                continue;
            }
            $lines = self::parseFeatures((string) ($item['features'] ?? $item['text'] ?? ''));
            if ($lines !== []) {
                $groups[] = $lines;
            }
        }
        if ($groups === []) {
            $groups[] = self::parseFeatures((string) ($content['features'] ?? ''));
        }

        return $groups;
    }

    /**
     * @return list<string>
     */
    private static function parseFeatures(string $raw): array
    {
        $lines = preg_split('/\r\n|\n|\r/', $raw) ?: [];

        return array_values(array_filter(array_map('trim', $lines), static fn (string $line): bool => $line !== ''));
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function isHighlighted(array $item): bool
    {
        $flag = strtolower(trim((string) ($item['highlighted'] ?? $item['badge'] ?? '')));

        return in_array($flag, ['1', 'true', 'yes', 'highlighted', 'mis en avant', 'recommandé'], true);
    }

    private static function formatPrice(string $price): string
    {
        $price = trim($price);
        if ($price === '') {
            return '';
        }
        if (str_starts_with($price, '$') || str_starts_with($price, '€')) {
            return $price;
        }

        return '$' . $price;
    }

    /**
     * @return list<array{title: string, features: list<array{title: string, info?: string, cells: array<string, string>}>}>
     */
    private static function pricing11Matrix(): array
    {
        return [
            [
                'title' => 'Vue d\'ensemble',
                'features' => [
                    [
                        'title' => 'Fonctionnalité toujours incluse',
                        'cells' => [
                            'Gratuit' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit.',
                            'Basique' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit.',
                            'Équipe' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit.',
                            'Entreprise' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit.',
                        ],
                    ],
                    [
                        'title' => 'Nombre de produits',
                        'info' => 'Texte d\'aide',
                        'cells' => [
                            'Gratuit' => '1',
                            'Basique' => '1',
                            'Équipe' => '3',
                            'Entreprise' => '5',
                        ],
                    ],
                    [
                        'title' => 'Nombre de transactions',
                        'info' => 'Texte d\'aide',
                        'cells' => [
                            'Gratuit' => '30 par mois',
                            'Basique' => 'Illimité',
                            'Équipe' => 'Illimité',
                            'Entreprise' => 'Illimité',
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Autres fonctionnalités',
                'features' => [
                    [
                        'title' => 'Fonctionnalité de base',
                        'cells' => [
                            'Gratuit' => 'check',
                            'Basique' => 'check',
                            'Équipe' => 'check',
                            'Entreprise' => 'check',
                        ],
                    ],
                    [
                        'title' => 'Fonctionnalité entreprise',
                        'info' => 'Texte d\'aide',
                        'cells' => [
                            'Gratuit' => 'x',
                            'Basique' => 'x',
                            'Équipe' => 'x',
                            'Entreprise' => 'check',
                        ],
                    ],
                    [
                        'title' => 'Fonctionnalité optionnelle',
                        'info' => 'Texte d\'aide',
                        'cells' => [
                            'Gratuit' => 'x',
                            'Basique' => 'x',
                            'Équipe' => 'addon',
                            'Entreprise' => 'addon',
                        ],
                    ],
                ],
            ],
        ];
    }
}
