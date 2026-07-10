<?php

declare(strict_types=1);

/**
 * Paramètres optionnels de déploiement en sous-dossier.
 * Laisser base_path vide pour une détection automatique via SCRIPT_NAME.
 * Forcer uniquement si l'hébergeur ne transmet pas SCRIPT_NAME correctement.
 */
return [
    'base_path' => '',
];
