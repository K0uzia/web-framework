<?php

declare(strict_types=1);

namespace App\Http\Admin;

final class PagesListRenderer
{
    /**
     * @param list<PageListRow> $rows
     */
    public function renderRows(array $rows): string
    {
        if ($rows === []) {
            return '';
        }

        $html = [];
        foreach ($rows as $row) {
            $html[] = $this->renderCard($row);
        }

        return implode('', $html);
    }

    private function renderCard(PageListRow $row): string
    {
        $title = htmlspecialchars($row->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $path = htmlspecialchars($row->path, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $relative = htmlspecialchars(RelativeTime::format($row->updatedAt), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $absolute = htmlspecialchars(RelativeTime::absolute($row->updatedAt), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $searchTitle = htmlspecialchars(mb_strtolower($row->title), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $searchPath = htmlspecialchars(mb_strtolower($row->path), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $editUrl = htmlspecialchars($row->editUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $previewUrl = htmlspecialchars($row->path, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $updatedAt = htmlspecialchars($row->updatedAt, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $initial = htmlspecialchars(mb_strtoupper(mb_substr($row->title, 0, 1)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<li class="admin-page-card" data-page-row data-title="' . $searchTitle . '" data-path="' . $searchPath . '">'
            . '<a class="admin-page-card__link" href="' . $editUrl . '" aria-label="Modifier ' . $title . '">'
            . '<span class="admin-page-card__icon" aria-hidden="true">' . $initial . '</span>'
            . '<span class="admin-page-card__body">'
            . '<span class="admin-page-card__title">' . $title . '</span>'
            . '<code class="admin-page-card__path">' . $path . '</code>'
            . '</span>'
            . '</a>'
            . '<div class="admin-page-card__footer">'
            . '<time class="admin-page-card__time" datetime="' . $updatedAt . '" title="' . $absolute . '">' . $relative . '</time>'
            . '<a class="admin-icon-btn admin-page-card__preview" href="' . $previewUrl . '" target="_blank" rel="noopener noreferrer" aria-label="Voir ' . $title . '" title="Voir la page">'
            . '<i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>'
            . '</a>'
            . '</div>'
            . '</li>';
    }
}
