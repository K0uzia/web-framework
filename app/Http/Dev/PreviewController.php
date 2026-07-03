<?php

declare(strict_types=1);

namespace App\Http\Dev;

use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\PageRenderer;

final class PreviewController
{
    public function __construct(private readonly PageRenderer $pages)
    {
    }

    public function show(Request $request, string $slug): Response
    {
        $decoded = SlugCodec::decode($slug);
        $path = $decoded === '' ? '/' : '/' . $decoded;

        return $this->pages->renderBySlug($decoded, [], $path, false);
    }
}
