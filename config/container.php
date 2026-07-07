<?php

declare(strict_types=1);

use App\Http\Dev\ExportController;
use App\Http\Dev\AuthController;
use App\Http\Dev\ChromeController;
use App\Http\Dev\MediaController;
use App\Http\Dev\FontUploader;
use App\Http\Dev\LibraryMediaUploader;
use App\Http\Dev\MediaUploader;
use App\Http\Dev\MediasController;
use App\Http\Dev\OverviewController;
use App\Http\Dev\PagesController;
use App\Http\Dev\PreviewController;
use App\Http\Dev\SectionFormRenderer;
use App\Http\Dev\SectionsController;
use App\Http\Dev\SiteController;
use App\Http\Dev\ThemeController;
use App\Http\Dev\VideoImportController;
use App\Http\HealthController;
use Capsule\Container;
use Capsule\Database;
use Capsule\DevDashboard;
use Capsule\Http\Factory\ResponseFactory;
use Capsule\Middleware\DevAuth;
use Capsule\Middleware\ErrorBoundary;
use Capsule\Middleware\SecurityHeaders;
use Capsule\LayoutRegistry;
use Capsule\MediaLibrary;
use Capsule\MediaRepository;
use Capsule\MediaUsageScanner;
use Capsule\PageRegistry;
use Capsule\PageRenderer;
use Capsule\PageRepository;
use Capsule\Router;
use Capsule\SectionRegistry;
use Capsule\SectionRenderer;
use Capsule\ProcessRunner;
use Capsule\SiteChrome;
use Capsule\ExportPathPicker;
use Capsule\SiteExportPath;
use Capsule\SiteExporter;
use Capsule\SiteRepository;
use Capsule\StylesheetResolver;
use Capsule\ThemePreviewRenderer;
use Capsule\VideoImportCleaner;
use Capsule\VideoImportConfig;
use Capsule\VideoImportProcessor;
use Capsule\VideoImportRepository;
use Capsule\VideoImportService;
use Capsule\VideoImportWorkerDispatcher;
use Capsule\VideoImportWorkerRunner;
use Capsule\VideoStreamResponder;
use Capsule\View;
use Capsule\YtDlpDownloader;
use Capsule\YouTubeUrlValidator;
use Capsule\FfmpegConverter;

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
        $c->get(SiteRepository::class),
    ));
    $c->set(AuthController::class, static fn (Container $c) => new AuthController(
        $c->get(DevDashboard::class),
        $c->get(ResponseFactory::class),
        $appConfig['dev_password'],
    ));
    $c->set(MediaRepository::class, static fn (Container $c) => new MediaRepository(
        $c->get(Database::class)->pdo(),
    ));
    $c->set(VideoImportConfig::class, static fn () => VideoImportConfig::fromEnv($root));
    $c->set(ProcessRunner::class, static fn () => new ProcessRunner());
    $c->set(YouTubeUrlValidator::class, static fn () => new YouTubeUrlValidator());
    $c->set(VideoImportRepository::class, static fn (Container $c) => new VideoImportRepository(
        $c->get(Database::class)->pdo(),
    ));
    $c->set(YtDlpDownloader::class, static fn (Container $c) => new YtDlpDownloader(
        $c->get(ProcessRunner::class),
        $c->get(VideoImportConfig::class),
        $c->get(YouTubeUrlValidator::class),
    ));
    $c->set(FfmpegConverter::class, static fn (Container $c) => new FfmpegConverter(
        $c->get(ProcessRunner::class),
        $c->get(VideoImportConfig::class),
    ));
    $c->set(VideoImportProcessor::class, static fn (Container $c) => new VideoImportProcessor(
        $c->get(VideoImportRepository::class),
        $c->get(MediaRepository::class),
        $c->get(VideoImportConfig::class),
        $c->get(YtDlpDownloader::class),
        $c->get(FfmpegConverter::class),
    ));
    $c->set(VideoImportCleaner::class, static fn (Container $c) => new VideoImportCleaner(
        $c->get(VideoImportConfig::class),
        $c->get(MediaRepository::class),
    ));
    $c->set(VideoImportWorkerRunner::class, static fn (Container $c) => new VideoImportWorkerRunner(
        $c->get(VideoImportRepository::class),
        $c->get(VideoImportProcessor::class),
    ));
    $c->set(VideoImportWorkerDispatcher::class, static fn (Container $c) => new VideoImportWorkerDispatcher(
        $root,
        $c->get(ProcessRunner::class),
        $appConfig['is_dev'],
    ));
    $c->set(VideoImportService::class, static fn (Container $c) => new VideoImportService(
        $c->get(VideoImportRepository::class),
        $c->get(VideoImportConfig::class),
        $c->get(YouTubeUrlValidator::class),
        $c->get(VideoImportCleaner::class),
    ));
    $c->set(VideoStreamResponder::class, static fn () => new VideoStreamResponder());
    $c->set(VideoImportController::class, static fn (Container $c) => new VideoImportController(
        $c->get(DevDashboard::class),
        $c->get(ResponseFactory::class),
        $c->get(VideoImportService::class),
        $c->get(VideoImportRepository::class),
        $c->get(VideoImportConfig::class),
        $c->get(VideoStreamResponder::class),
        $c->get(VideoImportWorkerRunner::class),
        $c->get(VideoImportWorkerDispatcher::class),
        $c->get(ProcessRunner::class),
        $c->get(YtDlpDownloader::class),
        $appConfig['is_dev'],
    ));
    $c->set(MediaUsageScanner::class, static fn (Container $c) => new MediaUsageScanner(
        $c->get(PageRepository::class),
        $c->get(SiteRepository::class),
    ));
    $c->set(LibraryMediaUploader::class, static fn () => new LibraryMediaUploader(
        $root . '/public/uploads/media',
    ));
    $c->set(MediaLibrary::class, static fn (Container $c) => new MediaLibrary(
        $c->get(MediaRepository::class),
        $root . '/public/uploads/site',
        '/uploads/site',
        $c->get(PageRepository::class),
        $c->get(SiteRepository::class),
    ));
    $c->set(SectionFormRenderer::class, static fn (Container $c) => new SectionFormRenderer(
        $c->get(SectionRegistry::class),
        $c->get(PageRepository::class),
        $c->get(MediaLibrary::class),
        $c->get(LibraryMediaUploader::class),
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
        $c->get(MediaUploader::class),
        $c->get(LibraryMediaUploader::class),
        $c->get(MediaLibrary::class),
        $c->get(MediaRepository::class),
    ));
    $c->set(MediasController::class, static fn (Container $c) => new MediasController(
        $c->get(DevDashboard::class),
        $c->get(MediaRepository::class),
        $c->get(MediaLibrary::class),
        $c->get(LibraryMediaUploader::class),
        $c->get(MediaUsageScanner::class),
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
    $c->set(ThemePreviewRenderer::class, static fn (Container $c) => new ThemePreviewRenderer(
        $c->get(ResponseFactory::class),
        $c->get(View::class),
        $c->get(SiteRepository::class),
        $c->get(SiteChrome::class),
        $c->get(StylesheetResolver::class),
        $appConfig['base_url'],
    ));
    $c->set(PreviewController::class, static fn (Container $c) => new PreviewController(
        $c->get(PageRenderer::class),
        $c->get(ThemePreviewRenderer::class),
    ));
    $c->set(OverviewController::class, static fn (Container $c) => new OverviewController(
        $c->get(DevDashboard::class),
        $c->get(PageRepository::class),
        $c->get(SiteRepository::class),
    ));
    $c->set(SiteExportPath::class, static fn () => new SiteExportPath($root));
    $c->set(ExportPathPicker::class, static fn () => new ExportPathPicker($root));
    $c->set(SiteExporter::class, static fn (Container $c) => new SiteExporter(
        $c->get(ResponseFactory::class),
        $c->get(View::class),
        $c->get(PageRepository::class),
        $c->get(SiteRepository::class),
        $c->get(SectionRenderer::class),
        $c->get(SiteChrome::class),
        $c->get(StylesheetResolver::class),
        $root,
        $root . '/public',
        $appConfig['base_url'],
    ));
    $c->set(ExportController::class, static fn (Container $c) => new ExportController(
        $c->get(DevDashboard::class),
        $c->get(SiteRepository::class),
        $c->get(SiteExporter::class),
        $c->get(SiteExportPath::class),
        $c->get(ExportPathPicker::class),
        $c->get(ResponseFactory::class),
        $root,
        $appConfig['base_url'],
        $appConfig['is_dev'],
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
