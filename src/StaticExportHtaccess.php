<?php

declare(strict_types=1);

namespace Capsule;

/**
 * Règles Apache pour un site statique exporté (URLs propres, index.html).
 */
final class StaticExportHtaccess
{
    public static function content(string $basePath = ''): string
    {
        $basePath = trim($basePath, '/');
        $rewriteBase = $basePath === '' ? '/' : '/' . $basePath . '/';

        $lines = [
            '# Généré par CapsulePHP lors de l\'export statique.',
            '# Apache : document root = dossier d\'export, AllowOverride activé.',
            '',
            'DirectoryIndex index.html',
            'Options -Indexes',
            '',
            '<IfModule mod_rewrite.c>',
            '    RewriteEngine On',
            '    RewriteBase ' . $rewriteBase,
            '',
            '    RewriteRule ^favicon\\.ico$ favicon.svg [L]',
            '',
            '    RewriteCond %{REQUEST_FILENAME} -f',
            '    RewriteRule ^ - [L]',
            '',
            '    RewriteCond %{REQUEST_FILENAME} !-f',
            '    RewriteCond %{REQUEST_URI} !/$',
            '    RewriteRule ^(.+)$ $1/ [R=301,L]',
            '',
            '    RewriteCond %{REQUEST_FILENAME} !-f',
            '    RewriteCond %{REQUEST_FILENAME}/index.html -f',
            '    RewriteRule ^(.*)$ $1/index.html [L]',
            '</IfModule>',
            '',
            '<IfModule mod_rewrite.c>',
            '    RewriteRule ^uploads/.+\\.php$ - [F,L]',
            '</IfModule>',
            '',
            '<IfModule mod_headers.c>',
            '    <FilesMatch "\\.(css|js|woff2?|ttf|eot|svg|png|jpe?g|gif|webp|ico)$">',
            '        Header set Cache-Control "public, max-age=31536000, immutable"',
            '    </FilesMatch>',
            '</IfModule>',
            '',
            '<IfModule mod_expires.c>',
            '    ExpiresActive On',
            '    ExpiresByType text/css "access plus 1 year"',
            '    ExpiresByType application/javascript "access plus 1 year"',
            '    ExpiresByType image/svg+xml "access plus 1 year"',
            '    ExpiresByType image/png "access plus 1 year"',
            '    ExpiresByType image/jpeg "access plus 1 year"',
            '    ExpiresByType image/webp "access plus 1 year"',
            '    ExpiresByType font/woff2 "access plus 1 year"',
            '</IfModule>',
            '',
        ];

        return implode("\n", $lines);
    }
}
