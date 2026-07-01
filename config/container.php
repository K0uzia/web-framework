<?php

declare(strict_types=1);

use App\Http\HealthController;
use Capsule\Container;
use Capsule\Http\Factory\ResponseFactory;
use Capsule\Middleware\ErrorBoundary;
use Capsule\Middleware\SecurityHeaders;
use Capsule\PageRenderer;
use Capsule\PageScanner;
use Capsule\Router;
use Capsule\StylesheetResolver;
use Capsule\View;

return (function (): Container {
    $c = new Container();
    $root = dirname(__DIR__);
    $resources = $root . '/resources';

    /** @var array{is_dev:bool,https:bool,app_name:string,base_url:string,password_min_length:int} $appConfig */
    $appConfig = require __DIR__ . '/app.php';

    $c->set('config', static fn () => $appConfig);
    $c->set(ResponseFactory::class, static fn () => new ResponseFactory());
    $c->set(View::class, static fn () => new View(
        $resources . '/layouts',
        $resources . '/partials',
    ));
    $c->set(StylesheetResolver::class, static fn () => new StylesheetResolver(
        $root . '/public/assets/css',
    ));
    $c->set(PageRenderer::class, static fn (Container $c) => new PageRenderer(
        $c->get(ResponseFactory::class),
        $c->get(View::class),
        $resources . '/layouts',
        $appConfig['base_url'],
        $c->get(StylesheetResolver::class),
    ));
    $c->set(HealthController::class, static fn (Container $c) => new HealthController(
        $c->get(ResponseFactory::class),
    ));
    $c->set(Router::class, static function (Container $c) use ($resources): Router {
        $scanner = new PageScanner();
        $discovered = $scanner->discover($resources . '/pages');

        $pageRoutes = [];
        foreach ($discovered['static'] as $key => $route) {
            $pageRoutes[$key] = ['page', $route->file];
        }

        $apiRoutes = require __DIR__ . '/routes.php';

        return new Router(
            array_merge($pageRoutes, $apiRoutes),
            $discovered['dynamic'],
            $c,
        );
    });
    $c->set(ErrorBoundary::class, static fn (Container $c) => new ErrorBoundary(
        $c->get(ResponseFactory::class),
        $appConfig['is_dev'],
        $appConfig['app_name'],
    ));
    $c->set(SecurityHeaders::class, static fn (Container $c) => new SecurityHeaders(
        $appConfig['is_dev'],
        $appConfig['https'],
    ));

    return $c;
})();
