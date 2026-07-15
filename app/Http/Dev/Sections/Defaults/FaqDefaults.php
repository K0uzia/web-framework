<?php

declare(strict_types=1);

namespace App\Http\Dev\Sections\Defaults;

trait FaqDefaults
{
    private static function faqContent(string $variant): array
    {
        $items = self::faqItems();

        return match ($variant) {
            'faq3' => [
                'title' => 'Questions fréquentes',
                'subtitle' => 'Trouvez des réponses aux questions courantes sur nos produits. Vous ne trouvez pas ce que vous cherchez ? Contactez notre équipe support.',
                'items' => $items,
            ],
            'faq5' => [
                'tagline' => 'FAQ',
                'title' => 'Questions et réponses courantes',
                'subtitle' => 'Découvrez les informations essentielles sur notre plateforme et la façon dont elle répond à vos besoins.',
                'items' => array_slice($items, 0, 4),
            ],
            default => [
                'title' => 'Questions fréquentes',
                'items' => $items,
            ],
        };
    }

    /**
     * @return list<array<string, string>>
     */
    private static function faqItems(): array
    {
        return [
            [
                'title' => 'Qu\'est-ce qu\'une FAQ ?',
                'text' => 'Une FAQ est une liste de questions fréquentes et de réponses sur un sujet donné.',
            ],
            [
                'title' => 'À quoi sert une FAQ ?',
                'text' => 'Elle permet de répondre rapidement aux questions courantes et d\'aider les utilisateurs à trouver l\'information sans attendre le support.',
            ],
            [
                'title' => 'Comment créer une FAQ efficace ?',
                'text' => 'Recensez les questions les plus fréquentes, rédigez des réponses claires et organisez-les de façon logique.',
            ],
            [
                'title' => 'Quels sont les avantages d\'une FAQ ?',
                'text' => 'Accès rapide à l\'information, moins de demandes au support et une meilleure expérience utilisateur.',
            ],
            [
                'title' => 'Comment organiser ma FAQ ?',
                'text' => 'Regroupez les questions par thème et classez-les du plus simple au plus avancé.',
            ],
            [
                'title' => 'Quelle longueur pour les réponses ?',
                'text' => 'Restez concis : quelques phrases ou un court paragraphe suffisent dans la plupart des cas.',
            ],
            [
                'title' => 'Faut-il inclure des liens ?',
                'text' => 'Oui, des liens vers des ressources détaillées aident les utilisateurs qui souhaitent approfondir un sujet.',
            ],
        ];
    }
}
