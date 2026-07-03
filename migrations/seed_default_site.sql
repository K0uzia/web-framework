INSERT OR REPLACE INTO site_settings (key, value, updated_at) VALUES (
    'site',
    '{"name":"CapsulePHP","tagline":"Framework PHP minimal pour sites composables.","home_label":"Accueil","footer_text":"© {year} {name}. Tous droits réservés.","partials":{"header":true,"footer":true},"nav_items":[],"nav_mode":"auto","header_cta":{"enabled":false,"label":"","href":""},"show_tagline_in_header":false,"logo_url":"","favicon_url":"","og_image_url":""}',
    datetime('now')
);

INSERT OR REPLACE INTO site_settings (key, value, updated_at) VALUES (
    'theme',
    '{"colors":{"primary":"#3b82f6","secondary":"#64748b","background":"#ffffff","text":"#0f172a","muted":"#f1f5f9","border":"#e2e8f0"},"fonts":{"heading":"Inter, system-ui, sans-serif","body":"system-ui, sans-serif"},"spacing":{"section":"4rem"},"layout":{"radius":"10px","container":"72rem"}}',
    datetime('now')
);

INSERT OR REPLACE INTO pages (slug, title, layout, description, sections, meta, published, updated_at) VALUES (
    '',
    'Framework PHP',
    'default',
    'Squelette PHP minimal, pages SQL et API déclarative.',
    '[{"id":"hero-1","type":"hero","variant":"centered","content":{"title":"Framework PHP","subtitle":"Bienvenue sur le squelette minimal CapsulePHP.","cta_label":"En savoir plus","cta_href":"#intro"},"style":{"bg":"primary","text_align":"center","padding":"xl"}},{"id":"features-1","type":"features","variant":"grid-3","content":{"items":[{"title":"Rapide","text":"Pages servies depuis SQLite, rendu léger."},{"title":"Simple","text":"Sections composables via le dashboard développeur."},{"title":"Fiable","text":"Framework PHP minimal sans dépendance runtime."}]},"style":{"bg":"muted","padding":"lg"}},{"id":"cta-1","type":"cta","variant":"banner","content":{"title":"Prêt à construire votre site ?","button_label":"Ouvrir le dashboard","button_href":"/dev"},"style":{"bg":"primary","padding":"lg"}}]',
    '{"schema_type":"WebPage","schema_name":"Capsule Micro"}',
    1,
    datetime('now')
);

INSERT OR REPLACE INTO pages (slug, title, layout, description, sections, meta, published, updated_at) VALUES (
    'about',
    'À propos',
    'default',
    'Découvrez CapsulePHP et son approche minimaliste.',
    '[{"id":"hero-about","type":"hero","variant":"centered","content":{"title":"À propos","subtitle":"CapsulePHP est un squelette léger pour construire des sites section par section.","cta_label":"Retour à l''accueil","cta_href":"/"},"style":{"bg":"background","text_align":"center","padding":"xl"}}]',
    '{"schema_type":"WebPage"}',
    1,
    datetime('now')
);
