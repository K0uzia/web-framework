<?php

declare(strict_types=1);

namespace Tests;

use Capsule\Container;
use Capsule\Http\Exception\HttpException;
use Capsule\Http\Factory\ResponseFactory;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\PageRenderer;
use Capsule\Router;
use Capsule\View;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Router::class)]
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

    public function testApiRouteWinsOverPageRoute(): void
    {
        $c = new Container();
        $c->set(ResponseFactory::class, static fn () => new ResponseFactory());
        $c->set(StubController::class, static fn (Container $c) => new StubController($c->get(ResponseFactory::class)));

        $router = new Router([
            'GET /api/health' => ['page', '/tmp/ignored.html'],
            'GET /api/health' => [StubController::class, 'ok'],
        ], [], $c);

        $response = $router->handle(new Request('GET', '/api/health', [], [], [], []));
        $this->assertSame('ok', $response->getBody());
    }

    public function test405WhenPathExistsButMethodNotAllowed(): void
    {
        $c = new Container();
        $c->set(ResponseFactory::class, static fn () => new ResponseFactory());
        $c->set(StubController::class, static fn (Container $c) => new StubController($c->get(ResponseFactory::class)));

        $router = new Router(['POST /submit' => [StubController::class, 'ok']], [], $c);

        try {
            $router->handle(new Request('GET', '/submit', [], [], [], []));
            $this->fail('Expected HttpException 405');
        } catch (HttpException $e) {
            $this->assertSame(405, $e->status);
            $this->assertArrayHasKey('Allow', $e->headers);
            $allow = $e->headers['Allow'][0] ?? '';
            $this->assertStringContainsString('POST', $allow);
        }
    }

    public function test404WhenPathUnknown(): void
    {
        $router = new Router([], [], new Container());

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Not Found');

        try {
            $router->handle(new Request('GET', '/missing', [], [], [], []));
        } catch (HttpException $e) {
            $this->assertSame(404, $e->status);
            throw $e;
        }
    }

    public function testPageHandlerDispatchesPageRenderer(): void
    {
        $root = sys_get_temp_dir() . '/capsule-router-page-' . bin2hex(random_bytes(4));
        mkdir($root . '/layouts');
        mkdir($root . '/pages');
        file_put_contents($root . '/layouts/default.html', '<html>{{{content}}}</html>');
        file_put_contents($root . '/pages/home.html', "---\ntitle: Hi\n---\n<p>{{title}}</p>\n");

        $c = new Container();
        $c->set(ResponseFactory::class, static fn () => new ResponseFactory());
        $c->set(View::class, static fn () => new View($root . '/layouts', ''));
        $c->set(PageRenderer::class, static fn (Container $c) => new PageRenderer(
            $c->get(ResponseFactory::class),
            $c->get(View::class),
            $root . '/layouts',
            'http://localhost:8080',
        ));

        $router = new Router(['GET /' => ['page', $root . '/pages/home.html']], [], $c);
        $response = $router->handle(new Request('GET', '/', [], [], [], []));

        $this->assertStringContainsString('<p>Hi</p>', (string) $response->getBody());

        @unlink($root . '/pages/home.html');
        @unlink($root . '/layouts/default.html');
        @rmdir($root . '/pages');
        @rmdir($root . '/layouts');
        @rmdir($root);
    }
}

final class StubController
{
    public function __construct(private readonly ResponseFactory $factory)
    {
    }

    public function ok(): Response
    {
        return $this->factory->text('ok');
    }
}
