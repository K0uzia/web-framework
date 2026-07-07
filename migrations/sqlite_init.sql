PRAGMA foreign_keys = ON;
PRAGMA journal_mode = DELETE;
PRAGMA synchronous = NORMAL;

CREATE TABLE IF NOT EXISTS pages (
    slug        TEXT PRIMARY KEY,
    title       TEXT NOT NULL,
    layout      TEXT NOT NULL DEFAULT 'default',
    description TEXT NOT NULL DEFAULT '',
    sections    TEXT NOT NULL DEFAULT '[]',
    meta        TEXT NOT NULL DEFAULT '{}',
    published   INTEGER NOT NULL DEFAULT 1,
    updated_at  TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS site_settings (
    key        TEXT PRIMARY KEY,
    value      TEXT NOT NULL,
    updated_at TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE INDEX IF NOT EXISTS idx_pages_published ON pages(published);

CREATE TABLE IF NOT EXISTS media (
    id         TEXT PRIMARY KEY,
    kind       TEXT NOT NULL CHECK (kind IN ('image', 'video')),
    url        TEXT NOT NULL UNIQUE,
    filename   TEXT NOT NULL,
    mime       TEXT NOT NULL DEFAULT '',
    size       INTEGER NOT NULL DEFAULT 0,
    label      TEXT NOT NULL DEFAULT '',
    created_at TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE INDEX IF NOT EXISTS idx_media_kind ON media(kind);
CREATE INDEX IF NOT EXISTS idx_media_created ON media(created_at);
