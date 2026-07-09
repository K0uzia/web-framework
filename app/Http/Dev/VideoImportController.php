<?php

declare(strict_types=1);

namespace App\Http\Dev;

use Capsule\DevDashboard;
use Capsule\Http\Factory\ResponseFactory;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\Http\Support\FormData;
use Capsule\ProcessRunner;
use Capsule\VideoImportConfig;
use Capsule\VideoImportRepository;
use Capsule\VideoImportService;
use Capsule\VideoImportWorkerDispatcher;
use Capsule\VideoImportWorkerRunner;
use Capsule\VideoStreamResponder;
use Capsule\YtDlpDownloader;

final class VideoImportController
{
    use DevHx;

    public function __construct(
        private readonly DevDashboard $ui,
        private readonly ResponseFactory $responses,
        private readonly VideoImportService $service,
        private readonly VideoImportRepository $imports,
        private readonly VideoImportConfig $config,
        private readonly VideoStreamResponder $stream,
        private readonly VideoImportWorkerRunner $worker,
        private readonly VideoImportWorkerDispatcher $dispatcher,
        private readonly ProcessRunner $processRunner,
        private readonly YtDlpDownloader $ytDlp,
        private readonly bool $isDev,
    ) {
    }

    public function index(Request $request): Response
    {
        $jobs = $this->imports->allRecent(30);
        $diag = $this->service->diagnostics($this->processRunner, $this->ytDlp);

        return $this->ui->render('video-imports.html', [
            'title' => 'Import vidéo',
            'crumb_html' => Breadcrumb::render([['label' => 'Import vidéo']]),
            'jobs_html' => $this->renderJobs($jobs),
            'worker_banner_html' => $this->renderWorkerBanner($diag),
            'require_approval' => $this->config->requireApproval ? '1' : '0',
            'max_mb' => (string) (int) ($this->config->maxFileBytes / 1024 / 1024),
            'flash' => $this->ui->flashFromRequest($request),
        ], section: 'video_imports');
    }

    public function store(Request $request): Response
    {
        $data = FormData::fromRequest($request);
        $rights = ($data['rights_accepted'] ?? '') === '1' || ($data['rights_accepted'] ?? '') === 'on';
        $label = trim((string) ($data['label'] ?? ''));
        $mode = (string) ($data['mode'] ?? 'upload');

        try {
            if ($mode === 'upload') {
                $file = $request->files['file'] ?? null;
                if (!is_array($file)) {
                    throw new \InvalidArgumentException('Aucun fichier reçu.');
                }
                $job = $this->service->enqueueUpload($file, $rights, label: $label);
            } else {
                $url = trim((string) ($data['url'] ?? ''));
                $job = $this->service->enqueueYouTube($url, $rights, label: $label);
            }

            $this->dispatchWorker();

            if ($this->wantsJson($request)) {
                return $this->responses->json([
                    'id' => $job['id'],
                    'status' => $job['status'],
                    'message' => $job['message'],
                ], 202);
            }

            return $this->ui->withFlash(
                $this->ui->redirect('/dev/video-imports'),
                'Import ajouté à la file. Traitement lancé en arrière-plan.',
            );
        } catch (\Throwable $e) {
            if ($this->wantsJson($request)) {
                return $this->responses->json(['error' => $e->getMessage()], 422);
            }

            return $this->ui->withFlash($this->ui->redirect('/dev/video-imports'), $e->getMessage());
        }
    }

    public function processQueue(Request $request): Response
    {
        $processed = $this->worker->processQueue(1);
        $message = $processed > 0
            ? 'Un import a été traité.'
            : 'Aucun import en attente dans la file.';

        if ($this->wantsJson($request)) {
            return $this->responses->json(['processed' => $processed, 'message' => $message]);
        }

        return $this->ui->withFlash($this->ui->redirect('/dev/video-imports'), $message);
    }

    public function destroy(Request $request, string $id): Response
    {
        try {
            $this->service->remove($id);
        } catch (\Throwable $e) {
            if ($this->wantsJson($request)) {
                return $this->responses->json(['error' => $e->getMessage()], 409);
            }

            return $this->ui->withFlash($this->ui->redirect('/dev/video-imports'), $e->getMessage());
        }

        if ($this->wantsJson($request)) {
            return $this->responses->json(['ok' => true]);
        }

        return $this->ui->withFlash($this->ui->redirect('/dev/video-imports'), 'Import annulé et supprimé.');
    }

    public function status(Request $request, string $id): Response
    {
        try {
            return $this->responses->json($this->service->statusPayload($id));
        } catch (\InvalidArgumentException $e) {
            return $this->responses->json(['error' => $e->getMessage()], 404);
        }
    }

    public function approve(Request $request, string $id): Response
    {
        if (!$this->service->approve($id)) {
            return $this->responses->json(['error' => 'Approbation impossible.'], 409);
        }

        $this->dispatchWorker();

        if ($this->wantsJson($request)) {
            return $this->responses->json(['ok' => true]);
        }

        return $this->ui->withFlash($this->ui->redirect('/dev/video-imports'), 'Import approuvé.');
    }

    public function stream(Request $request, string $id): Response
    {
        $job = $this->imports->findById($id);
        if ($job === null || $job['status'] !== 'ready' || $job['video_path'] === '') {
            return new Response(404, ['Content-Type' => 'text/plain; charset=utf-8'], 'Vidéo indisponible');
        }

        return $this->stream->respond($request, (string) $job['video_path']);
    }

    /**
     * @param array{yt_dlp: bool, ffmpeg: bool, queued: int, pending: int, yt_dlp_version?: string, yt_dlp_outdated?: bool, yt_dlp_bin?: string} $diag
     */
    private function renderWorkerBanner(array $diag): string
    {
        $issues = [];
        if (!$diag['yt_dlp']) {
            $issues[] = 'yt-dlp introuvable';
        }
        if (!$diag['ffmpeg']) {
            $issues[] = 'ffmpeg introuvable';
        }
        if (!empty($diag['yt_dlp_outdated'])) {
            $version = (string) ($diag['yt_dlp_version'] ?? '');
            $issues[] = 'yt-dlp obsolète' . ($version !== '' ? ' (' . $version . ')' : '');
        }

        $html = '<div class="dev-video-import-worker">';
        if ($issues !== []) {
            $html .= '<p class="dev-video-import-worker__alert" role="status"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i> '
                . htmlspecialchars(implode(' · ', $issues), ENT_QUOTES)
                . '. Mettez à jour : <code>bash scripts/install-video-tools.sh</code></p>';
            if (!empty($diag['yt_dlp_bin'])) {
                $html .= '<p class="dev-video-import-worker__hint">Binaire actuel : <code>'
                    . htmlspecialchars((string) $diag['yt_dlp_bin'], ENT_QUOTES) . '</code></p>';
            }
        }

        $queued = (int) $diag['queued'];
        if ($queued > 0) {
            $html .= '<p class="dev-video-import-worker__hint">'
                . $queued . ' import(s) en file. Le traitement démarre automatiquement en dev, ou utilisez le bouton ci-dessous.</p>'
                . '<form method="post" action="/dev/api/videos/process-queue" class="dev-inline-form">'
                . '<button type="submit" class="dev-button dev-button--secondary dev-button--sm">'
                . '<i class="fa-solid fa-play" aria-hidden="true"></i> Traiter un import maintenant</button></form>';
        } elseif ($issues === []) {
            $html .= '<p class="dev-video-import-worker__ok" role="status"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Outils vidéo détectés. Worker prêt.</p>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * @param list<array<string, mixed>> $jobs
     */
    private function renderJobs(array $jobs): string
    {
        if ($jobs === []) {
            return '<p class="dev-empty"><i class="fa-solid fa-clapperboard" aria-hidden="true"></i>Aucun import pour le moment.</p>';
        }

        $rows = [];
        foreach ($jobs as $job) {
            $id = htmlspecialchars((string) $job['id'], ENT_QUOTES);
            $status = htmlspecialchars((string) $job['status'], ENT_QUOTES);
            $rawStatus = (string) $job['status'];
            $title = htmlspecialchars((string) ($job['title'] !== '' ? $job['title'] : $job['user_label']), ENT_QUOTES);
            $message = htmlspecialchars((string) $job['message'], ENT_QUOTES);
            $progress = (int) $job['progress'];
            $source = htmlspecialchars((string) $job['source'], ENT_QUOTES);
            $thumb = (string) $job['public_thumb_url'];
            $video = (string) $job['public_video_url'];

            $player = '';
            if ($job['status'] === 'ready' && $video !== '') {
                $player = '<video class="dev-video-import__player" controls preload="metadata" playsinline src="/dev/api/videos/' . $id . '/stream"></video>';
            } elseif ($thumb !== '') {
                $player = '<img class="dev-video-import__thumb" src="' . htmlspecialchars($thumb, ENT_QUOTES) . '" alt="" loading="lazy" decoding="async" />';
            } else {
                $player = '<div class="dev-video-import__placeholder" aria-hidden="true"><i class="fa-solid fa-film"></i></div>';
            }

            $actions = '';
            if ($job['status'] === 'pending_approval') {
                $actions .= '<form method="post" action="/dev/api/videos/' . $id . '/approve" data-dev-video-approve class="dev-inline-form">'
                    . '<button type="submit" class="dev-button dev-button--secondary dev-button--sm">Approuver</button></form>';
            }

            if (in_array($rawStatus, ['queued', 'pending_approval', 'failed', 'ready'], true)) {
                $actions .= '<form method="post" action="/dev/video-imports/' . $id . '/delete" class="dev-inline-form" data-dev-video-delete>'
                    . '<button type="submit" class="dev-icon-btn dev-icon-btn--danger" aria-label="Annuler et supprimer" title="Annuler et supprimer">'
                    . '<i class="fa-solid fa-trash" aria-hidden="true"></i></button></form>';
            }

            $meta = '<p class="dev-video-import__meta"><code>' . $id . '</code> · ' . $source . ' · ' . $progress . ' %</p>';

            $rows[] = '<article class="dev-video-import" data-dev-video-import data-video-id="' . $id . '" data-video-status="' . $status . '">'
                . '<div class="dev-video-import__visual">' . $player . '</div>'
                . '<div class="dev-video-import__body">'
                . '<div class="dev-video-import__head">'
                . '<h3 class="dev-video-import__title">' . $title . '</h3>'
                . '<div class="dev-video-import__actions">' . $actions . '</div>'
                . '</div>'
                . $meta
                . '<p class="dev-video-import__status" data-dev-video-status>' . $status . ' : ' . $message . '</p>'
                . '<div class="dev-video-import__progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="' . $progress . '">'
                . '<span style="width:' . $progress . '%"></span></div>'
                . '</div></article>';
        }

        return '<div class="dev-video-import-list">' . implode('', $rows) . '</div>';
    }

    private function dispatchWorker(): void
    {
        if ($this->isDev) {
            $this->dispatcher->dispatchOnce();
        }
    }

    private function wantsJson(Request $request): bool
    {
        $accept = $request->headers['accept'] ?? $request->headers['Accept'] ?? '';

        return str_contains((string) $accept, 'application/json')
            || ($request->headers['HX-Request'] ?? '') === 'true';
    }
}
