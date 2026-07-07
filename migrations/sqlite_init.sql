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

CREATE TABLE IF NOT EXISTS video_imports (
    id                TEXT PRIMARY KEY,
    source            TEXT NOT NULL CHECK (source IN ('youtube', 'upload')),
    source_url        TEXT NOT NULL DEFAULT '',
    youtube_id        TEXT NOT NULL DEFAULT '',
    user_label        TEXT NOT NULL DEFAULT '',
    title             TEXT NOT NULL DEFAULT '',
    duration_sec      INTEGER NOT NULL DEFAULT 0,
    video_path        TEXT NOT NULL DEFAULT '',
    thumb_path        TEXT NOT NULL DEFAULT '',
    public_video_url  TEXT NOT NULL DEFAULT '',
    public_thumb_url  TEXT NOT NULL DEFAULT '',
    media_id          TEXT NOT NULL DEFAULT '',
    status            TEXT NOT NULL DEFAULT 'queued' CHECK (status IN ('queued', 'downloading', 'converting', 'ready', 'failed', 'pending_approval')),
    progress          INTEGER NOT NULL DEFAULT 0,
    message           TEXT NOT NULL DEFAULT '',
    rights_accepted   INTEGER NOT NULL DEFAULT 0,
    requires_approval INTEGER NOT NULL DEFAULT 0,
    approved          INTEGER NOT NULL DEFAULT 1,
    attempts          INTEGER NOT NULL DEFAULT 0,
    max_attempts      INTEGER NOT NULL DEFAULT 3,
    file_size         INTEGER NOT NULL DEFAULT 0,
    format            TEXT NOT NULL DEFAULT 'mp4',
    owner_id          TEXT NOT NULL DEFAULT 'dev',
    created_at        TEXT NOT NULL DEFAULT (datetime('now')),
    updated_at        TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE INDEX IF NOT EXISTS idx_video_imports_status ON video_imports(status);
CREATE INDEX IF NOT EXISTS idx_video_imports_owner ON video_imports(owner_id);
CREATE INDEX IF NOT EXISTS idx_video_imports_created ON video_imports(created_at);
