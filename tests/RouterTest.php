<?php

declare(strict_types=1);

namespace Tests;

use App\Http\Dev\AuthController;
use Capsule\Container;
use Capsule\Http\Factory\ResponseFactory;
use Capsule\Http\Message\Request;
use Capsule\PageRegistry;
use Capsule\PageRenderer;
use Capsule\PageRepository;
use Capsule\Router;
use Capsule\SectionRenderer;
use Capsule\SiteChrome;
use Capsule\SiteRepository;
use Capsule\ScriptResolver;
use Capsule\StylesheetResolver;
use Capsule\View;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    public function testExactMatchDispatchesController(): void
    {
        $c = new Container();
        $c->set(ResponseFactory::class, static fn () => new ResponseFactory());
        $c->set(StubController::class, static fn (Container $c) => new StubController($c->get(ResponseFactory::class)));

        $router = new Router(['GET /' => [StubController::class, 'ok']], [], $c);
        $response = $router->handle(new Request('GET', '/', [], [], [], []));

        $this->assertSame(200, $response->getStatus());
        $this->assertSame('ok', $response->getBody());
    }

    public function testSubfolderPrefixIsStripped(): void
    {
        $c = new Container();
        $c->set(ResponseFactory::class, static fn () => new ResponseFactory());
        $c->set(StubController::class, static fn (Container $c) => new StubController($c->get(ResponseFactory::class)));

        $router = new Router(['GET /' => [StubController::class, 'ok']], [], $c);
        $response = $router->handle(new Request('GET', '/', [], [], [], []));

        $this->assertSame(200, $response->getStatus());
        $this->assertSame('ok', $response->getBody());
    }

    public function testApiHealthPathIsResolved(): void
    {
        $c = new Container();
        $c->set(ResponseFactory::class, static fn () => new ResponseFactory());
        $c->set(StubController::class, static fn (Container $c) => new StubController($c->get(ResponseFactory::class)));

        $router = new Router(['GET /api/health' => [StubController::class, 'ok']], [], $c);
        $response = $router->handle(new Request('GET', '/api/health', [], [], [], []));

        $this->assertSame(200, $response->getStatus());
    }

    public function testPatternRoutePassesSlug(): void
    {
        $c = new Container();
        $c->set(ResponseFactory::class, static fn () => new ResponseFactory());
        $c->set(SlugEchoController::class, static fn (Container $c) => new SlugEchoController($c->get(ResponseFactory::class)));

        $router = new Router([], [], $c, [[
            'methods' => ['GET'],
            'pattern' => '#^/dev/pages/(?<slug>[^/]+)$#',
            'handler' => [SlugEchoController::class, 'show'],
        ]]);

        $response = $router->handle(new Request('GET', '/dev/pages/about', [], [], [], []));
        $this->assertSame('slug:about', $response->getBody());
    }

    public function testPageHandlerDispatchesPageRenderer(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec(file_get_contents(dirname(__DIR__) . '/migrations/sqlite_init.sql') ?: '');
        $pdo->exec(file_get_contents(dirname(__DIR__) . '/migrations/seed_default_site.sql') ?: '');

        $root = dirname(__DIR__);
        $resources = $root . '/resources';
        $c = new Container();
        $c->set(ResponseFactory::class, static fn () => new ResponseFactory());
        $c->set(PageRepository::class, static fn () => new PageRepository($pdo));
        $c->set(SiteRepository::class, static fn () => new SiteRepository($pdo));
        $c->set(View::class, static fn () => new View($resources . '/layouts', $resources . '/partials'));
        $c->set(StylesheetResolver::class, static fn () => new StylesheetResolver($root . '/public/assets/css'));
        $c->set(ScriptResolver::class, static fn () => new ScriptResolver($root . '/public/assets/js'));
        $c->set(SectionRenderer::class, static fn (Container $c) => new SectionRenderer(
            $c->get(View::class),
            $resources . '/sections',
            true,
        ));
        $c->set(SiteChrome::class, static fn (Container $c) => new SiteChrome(
            $c->get(PageRepository::class),
            $c->get(SiteRepository::class),
            $c->get(View::class),
            'CapsulePHP',
        ));
        $c->set(PageRenderer::class, static fn (Container $c) => new PageRenderer(
            $c->get(ResponseFactory::class),
            $c->get(View::class),
            $c->get(PageRepository::class),
            $c->get(SiteRepository::class),
            $c->get(SectionRenderer::class),
            $c->get(SiteChrome::class),
            'http://localhost:8080',
            $c->get(StylesheetResolver::class),
            $c->get(ScriptResolver::class),
            $root . '/public/assets/css',
        ));
        $c->set(PageRegistry::class, static fn (Container $c) => new PageRegistry($c->get(PageRepository::class)));
        $c->set(Router::class, static function (Container $c): Router {
            $discovered = $c->get(PageRegistry::class)->routes();
            $pageRoutes = [];
            foreach ($discovered['static'] as $key => $route) {
                $pageRoutes[$key] = ['page', $route->slug];
            }

            return new Router($pageRoutes, [], $c);
        });

        $router = $c->get(Router::class);
        $response = $router->handle(new Request('GET', '/', [], [], [], []));

        $this->assertStringContainsString('Framework PHP', (string) $response->getBody());
    }
}

final class StubController
{
    public function __construct(private readonly ResponseFactory $factory)
    {
    }

    public function ok(\Capsule\Http\Message\Request $request): \Capsule\Http\Message\Response
    {
        return $this->factory->text('ok');
    }
}

final class SlugEchoController
{
    public function __construct(private readonly ResponseFactory $factory)
    {
    }

    public function show(\Capsule\Http\Message\Request $request, string $slug): \Capsule\Http\Message\Response
    {
        return $this->factory->text('slug:' . $slug);
    }
}

final class DevAuthControllerTest extends TestCase
{
    public function testLoginSetsCookie(): void
    {
        $ui = new DevDashboard(dirname(__DIR__, 2) . '/resources/dev', new ResponseFactory());
        $auth = new AuthController($ui, new ResponseFactory(), 'secret');

        $response = $auth->login(new Request(
            'POST',
            '/dev/login',
            [],
            ['Content-Type' => 'application/x-www-form-urlencoded'],
            [],
            [],
            rawBody: 'password=secret',
        ));

        $this->assertSame(302, $response->getStatus());
        $cookies = $response->getHeader('Set-Cookie');
        $this->assertNotEmpty($cookies);
        $this->assertStringContainsString('capsule_dev=1', $cookies[0]);
    }
}
