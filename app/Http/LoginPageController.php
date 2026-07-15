<?php

declare(strict_types=1);

namespace App\Http;

use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\LoginPageRenderer;

final class LoginPageController
{
    public function __construct(private readonly LoginPageRenderer $pages)
    {
    }

    public function show(Request $request): Response
    {
        return $this->pages->render('/login');
    }
}
