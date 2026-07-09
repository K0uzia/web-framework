<?php

declare(strict_types=1);

namespace Capsule;

use PDO;

final class PageRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findBySlug(string $slug, bool $publishedOnly = true): ?Page
    {
        $sql = 'SELECT slug, title, layout, description, sections, meta, published, updated_at FROM pages WHERE slug = :slug';
        if ($publishedOnly) {
            $sql .= ' AND published = 1';
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $this->hydrate($row) : null;
    }

    /**
     * @return list<Page>
     */
    public function allPublished(): array
    {
        $stmt = $this->pdo->query(
            'SELECT slug, title, layout, description, sections, meta, published, updated_at FROM pages WHERE published = 1 ORDER BY slug',
        );
        if ($stmt === false) {
            return [];
        }

        $pages = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (is_array($row)) {
                $pages[] = $this->hydrate($row);
            }
        }

        return $pages;
    }

    /**
     * @return list<Page>
     */
    public function all(): array
    {
        $stmt = $this->pdo->query(
            'SELECT slug, title, layout, description, sections, meta, published, updated_at FROM pages ORDER BY slug',
        );
        if ($stmt === false) {
            return [];
        }

        $pages = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (is_array($row)) {
                $pages[] = $this->hydrate($row);
            }
        }

        return $pages;
    }

    public function save(Page $page): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO pages (slug, title, layout, description, sections, meta, published, updated_at)
             VALUES (:slug, :title, :layout, :description, :sections, :meta, :published, datetime(\'now\'))
             ON CONFLICT(slug) DO UPDATE SET
                title = excluded.title,
                layout = excluded.layout,
                description = excluded.description,
                sections = excluded.sections,
                meta = excluded.meta,
                published = excluded.published,
                updated_at = datetime(\'now\')',
        );

        $stmt->execute([
            'slug' => $page->slug,
            'title' => $page->title,
            'layout' => $page->layout,
            'description' => $page->description,
            'sections' => json_encode($page->sections, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            'meta' => json_encode($page->meta, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            'published' => $page->published ? 1 : 0,
        ]);
    }

    public function delete(string $slug): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM pages WHERE slug = :slug');
        $stmt->execute(['slug' => $slug]);
    }

    public function setHomePage(string $slug): void
    {
        if ($slug === '') {
            return;
        }

        $this->pdo->beginTransaction();

        try {
            $temp = '__home_' . bin2hex(random_bytes(4));
            $stmt = $this->pdo->prepare('UPDATE pages SET slug = :new WHERE slug = :old');

            $stmt->execute(['new' => $temp, 'old' => $slug]);
            if ($stmt->rowCount() === 0) {
                $this->pdo->rollBack();

                return;
            }

            if ($this->findBySlug('', false) !== null) {
                $stmt->execute(['new' => $slug, 'old' => '']);
            }

            $stmt->execute(['new' => '', 'old' => $temp]);
            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();

            throw $e;
        }
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): Page
    {
        $sections = json_decode((string) $row['sections'], true);
        $meta = json_decode((string) $row['meta'], true);

        return new Page(
            slug: (string) $row['slug'],
            title: (string) $row['title'],
            layout: (string) $row['layout'],
            description: (string) $row['description'],
            sections: is_array($sections) ? $sections : [],
            meta: is_array($meta) ? $meta : [],
            published: (int) $row['published'] === 1,
            updatedAt: (string) $row['updated_at'],
        );
    }
}
