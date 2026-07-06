<?php

declare(strict_types=1);

use App\Http\Dev\AuthController;
use App\Http\Dev\ChromeController;
use App\Http\Dev\MediaController;
use App\Http\Dev\FontUploader;
use App\Http\Dev\MediaUploader;
use App\Http\Dev\OverviewController;
use App\Http\Dev\PagesController;
use App\Http\Dev\PreviewController;
use App\Http\Dev\SectionFormRenderer;
use App\Http\Dev\SectionsController;
use App\Http\Dev\SiteController;
use App\Http\Dev\ThemeController;
use App\Http\HealthController;
use Capsule\Container;
use Capsule\Database;
use Capsule\DevDashboard;
use Capsule\Http\Factory\ResponseFactory;
use Capsule\Middleware\DevAuth;
use Capsule\Middleware\ErrorBoundary;
use Capsule\Middleware\SecurityHeaders;
use Capsule\LayoutRegistry;
use Capsule\PageRegistry;
use Capsule\PageRenderer;
use Capsule\PageRepository;
use Capsule\Router;
use Capsule\SectionRegistry;
use Capsule\SectionRenderer;
use Capsule\SiteChrome;
use Capsule\SiteRepository;
use Capsule\StylesheetResolver;
use Capsule\View;

return (function (): Container {
    $c = new Container();
    $root = dirname(__DIR__);
    $resources = $root . '/resources';

    /** @var array{is_dev:bool,https:bool,app_name:string,base_url:string,password_min_length:int,dev_password:string} $appConfig */
    $appConfig = require __DIR__ . '/app.php';

    $c->set('config', static fn () => $appConfig);
    $c->set(ResponseFactory::class, static fn () => new ResponseFactory());
    $c->set(Database::class, static fn () => Database::fromConfig());
    $c->set(PageRepository::class, static fn (Container $c) => new PageRepository(
        $c->get(Database::class)->pdo(),
    ));
    $c->set(SiteRepository::class, static fn (Container $c) => new SiteRepository(
        $c->get(Database::class)->pdo(),
    ));
    $c->set(SectionRegistry::class, static fn () => new SectionRegistry(
        $resources . '/sections/registry.yaml',
    ));
    $c->set(View::class, static fn () => new View(
        $resources . '/layouts',
        $resources . '/partials',
    ));
    $c->set(StylesheetResolver::class, static fn () => new StylesheetResolver(
        $root . '/public/assets/css',
    ));
    $c->set(SectionRenderer::class, static fn (Container $c) => new SectionRenderer(
        $c->get(View::class),
        $resources . '/sections',
        $appConfig['is_dev'],
    ));
    $c->set(SiteChrome::class, static fn (Container $c) => new SiteChrome(
        $c->get(PageRepository::class),
        $c->get(SiteRepository::class),
        $c->get(View::class),
        $appConfig['app_name'],
    ));
    $c->set(LayoutRegistry::class, static fn () => new LayoutRegistry(
        $resources . '/layouts',
    ));
    $c->set(PageRenderer::class, static fn (Container $c) => new PageRenderer(
        $c->get(ResponseFactory::class),
        $c->get(View::class),
        $c->get(PageRepository::class),
        $c->get(SiteRepository::class),
        $c->get(SectionRenderer::class),
        $c->get(SiteChrome::class),
        $appConfig['base_url'],
        $c->get(StylesheetResolver::class),
    ));
    $c->set(PageRegistry::class, static fn (Container $c) => new PageRegistry(
        $c->get(PageRepository::class),
    ));
    $c->set(HealthController::class, static fn (Container $c) => new HealthController(
        $c->get(ResponseFactory::class),
    ));
    $c->set(DevDashboard::class, static fn (Container $c) => new DevDashboard(
        $resources . '/dev',
        $c->get(ResponseFactory::class),
    ));
    $c->set(AuthController::class, static fn (Container $c) => new AuthController(
        $c->get(DevDashboard::class),
        $c->get(ResponseFactory::class),
        $appConfig['dev_password'],
    ));
    $c->set(SectionFormRenderer::class, static fn (Container $c) => new SectionFormRenderer(
        $c->get(SectionRegistry::class),
        $c->get(PageRepository::class),
    ));
    $c->set(PagesController::class, static fn (Container $c) => new PagesController(
        $c->get(DevDashboard::class),
        $c->get(PageRepository::class),
        $c->get(SiteRepository::class),
        $c->get(SectionRegistry::class),
        $c->get(SectionFormRenderer::class),
        $c->get(LayoutRegistry::class),
    ));
    $c->set(SectionsController::class, static fn (Container $c) => new SectionsController(
        $c->get(DevDashboard::class),
        $c->get(PageRepository::class),
        $c->get(SectionRegistry::class),
        $c->get(SectionFormRenderer::class),
    ));
    $c->set(MediaUploader::class, static fn () => new MediaUploader(
        $root . '/public/uploads/site',
    ));
    $c->set(FontUploader::class, static fn () => new FontUploader(
        $root . '/public/uploads/fonts',
    ));
    $c->set(MediaController::class, static fn (Container $c) => new MediaController(
        $c->get(DevDashboard::class),
        $c->get(SiteRepository::class),
        $c->get(MediaUploader::class),
        $c->get(ResponseFactory::class),
    ));
    $c->set(ChromeController::class, static fn (Container $c) => new ChromeController(
        $c->get(DevDashboard::class),
        $c->get(SiteRepository::class),
        $c->get(PageRepository::class),
    ));
    $c->set(SiteController::class, static fn (Container $c) => new SiteController(
        $c->get(DevDashboard::class),
        $c->get(SiteRepository::class),
        $c->get(PageRepository::class),
        $c->get(MediaUploader::class),
    ));
    $c->set(ThemeController::class, static fn (Container $c) => new ThemeController(
        $c->get(DevDashboard::class),
        $c->get(SiteRepository::class),
        $c->get(FontUploader::class),
    ));
    $c->set(PreviewController::class, static fn (Container $c) => new PreviewController(
        $c->get(PageRenderer::class),
    ));
    $c->set(OverviewController::class, static fn (Container $c) => new OverviewController(
        $c->get(DevDashboard::class),
        $c->get(PageRepository::class),
        $c->get(SiteRepository::class),
    ));
    $c->set(Router::class, static function (Container $c): Router {
        $registry = $c->get(PageRegistry::class);
        $discovered = $registry->routes();

        $pageRoutes = [];
        foreach ($discovered['static'] as $key => $route) {
            $pageRoutes[$key] = ['page', $route->slug];
        }

        $apiRoutes = require __DIR__ . '/routes.php';
        $split = Router::splitRoutes($apiRoutes);

        return new Router(
            array_merge($pageRoutes, $split['exact']),
            $discovered['dynamic'],
            $c,
            $split['patterns'],
        );
    });
    $c->set(ErrorBoundary::class, static fn (Container $c) => new ErrorBoundary(
        $c->get(ResponseFactory::class),
        $appConfig['is_dev'],
        $appConfig['app_name'],
    ));
    $c->set(DevAuth::class, static fn (Container $c) => new DevAuth(
        $c->get(ResponseFactory::class),
        $appConfig['dev_password'],
        $appConfig['is_dev'],
    ));
    $c->set(SecurityHeaders::class, static fn (Container $c) => new SecurityHeaders(
        $appConfig['is_dev'],
        $appConfig['https'],
    ));

    return $c;
})();
