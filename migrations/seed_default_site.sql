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
    'Accueil',
    'default',
    'Sections hero composables via le dashboard développeur.',
    '[{"id":"hero-1","type":"hero","variant":"hero3","content":{"title":"Des blocs prêts à l''emploi","subtitle":"Composants soignés pour vos pages marketing. Copiez, adaptez et publiez en quelques minutes.","image_url":"/assets/sections/hero/_shared/saas-hero-1-16x9.png","image_url_dark":"/assets/sections/hero/_shared/saas-hero-1-16x9-dark.png","image_alt":"Aperçu produit","reviews_rating":"5.0","reviews_count":"200","review_avatars":[{"url":"/assets/sections/hero/_shared/avatars/avatar1.jpg","title":"Mia Chen"},{"url":"/assets/sections/hero/_shared/avatars/avatar2.jpg","title":"Marcus Rivera"},{"url":"/assets/sections/hero/_shared/avatars/avatar3.jpg","title":"Priya Sharma"},{"url":"/assets/sections/hero/_shared/avatars/avatar4.jpg","title":"James Okafor"},{"url":"/assets/sections/hero/_shared/avatars/avatar5.jpg","title":"Sofia Chen"}],"buttons":[{"label":"Parcourir","href":"#","style":"primary"},{"label":"Voir le code","href":"#","style":"secondary"}]},"style":{"bg":"background","padding":"xl"}}]',
    '{"schema_type":"WebPage","schema_name":"Capsule Micro"}',
    1,
    datetime('now')
);

INSERT OR REPLACE INTO pages (slug, title, layout, description, sections, meta, published, updated_at) VALUES (
    'about',
    'À propos',
    'default',
    'Découvrez CapsulePHP et son approche minimaliste.',
    '[{"id":"hero-about","type":"hero","variant":"hero3","content":{"title":"À propos","subtitle":"CapsulePHP est un squelette léger pour construire des sites section par section.","buttons":[{"label":"Retour à l''accueil","href":"/","style":"primary"}]},"style":{"bg":"background","padding":"xl"}}]',
    '{"schema_type":"WebPage"}',
    1,
    datetime('now')
);
