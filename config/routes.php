<?php

declare(strict_types=1);

use App\Http\Dev\AuthController;
use App\Http\Dev\MediaController;
use App\Http\Dev\OverviewController;
use App\Http\Dev\PagesController;
use App\Http\Dev\PreviewController;
use App\Http\Dev\SectionsController;
use App\Http\Dev\SiteController;
use App\Http\Dev\ThemeController;
use App\Http\HealthController;

$devRoutes = [
    'GET /dev' => [AuthController::class, 'loginForm'],
    'POST /dev/login' => [AuthController::class, 'login'],
    'POST /dev/logout' => [AuthController::class, 'logout'],
    'GET /dev/overview' => [OverviewController::class, 'index'],
    'GET /dev/pages' => [PagesController::class, 'index'],
    'GET /dev/pages/new' => [PagesController::class, 'createForm'],
    'POST /dev/pages' => [PagesController::class, 'store'],
    'GET /dev/pages/{slug}' => [PagesController::class, 'edit'],
    'POST /dev/pages/{slug}' => [PagesController::class, 'update'],
    'POST /dev/pages/{slug}/delete' => [PagesController::class, 'destroy'],
    'POST /dev/pages/{slug}/duplicate' => [PagesController::class, 'duplicate'],
    'POST /dev/pages/{slug}/rename' => [PagesController::class, 'rename'],
    'POST /dev/pages/{slug}/sections/reorder' => [SectionsController::class, 'reorder'],
    'POST /dev/pages/{slug}/sections' => [SectionsController::class, 'add'],
    'POST /dev/pages/{slug}/sections/{id}' => [SectionsController::class, 'update'],
    'POST /dev/pages/{slug}/sections/{id}/move' => [SectionsController::class, 'move'],
    'POST /dev/pages/{slug}/sections/{id}/delete' => [SectionsController::class, 'destroy'],
    'GET /dev/site' => [SiteController::class, 'edit'],
    'POST /dev/site' => [SiteController::class, 'update'],
    'POST /dev/site/nav' => [SiteController::class, 'updateNav'],
    'POST /dev/site/nav/add' => [SiteController::class, 'addNav'],
    'POST /dev/site/nav/sync' => [SiteController::class, 'syncNav'],
    'POST /dev/site/nav/reset' => [SiteController::class, 'resetNav'],
    'POST /dev/site/nav/reorder' => [SiteController::class, 'reorderNav'],
    'POST /dev/site/nav/{id}/move' => [SiteController::class, 'moveNav'],
    'POST /dev/site/nav/{id}/delete' => [SiteController::class, 'deleteNav'],
    'POST /dev/media/{field}/upload' => [MediaController::class, 'upload'],
    'POST /dev/media/{field}/remove' => [MediaController::class, 'remove'],
    'GET /dev/theme' => [ThemeController::class, 'edit'],
    'POST /dev/theme' => [ThemeController::class, 'update'],
    'POST /dev/theme/reset' => [ThemeController::class, 'reset'],
    'POST /dev/theme/fonts' => [ThemeController::class, 'uploadFont'],
    'POST /dev/theme/fonts/{id}/remove' => [ThemeController::class, 'removeFont'],
    'GET /dev/preview/{slug}' => [PreviewController::class, 'show'],
];

return array_merge($devRoutes, [
    'GET /api/health' => [HealthController::class, 'health'],
]);
