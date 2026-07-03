<?php

declare(strict_types=1);

namespace App\Http\Dev;

use Capsule\DevDashboard;
use Capsule\Http\Factory\ResponseFactory;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\Http\Support\Cookie;
use Capsule\Http\Support\FormData;

final class AuthController
{
    public function __construct(
        private readonly DevDashboard $ui,
        private readonly ResponseFactory $responses,
        private readonly string $devPassword,
    ) {
    }

    public function loginForm(Request $request): Response
    {
        if (($request->cookies['capsule_dev'] ?? '') === '1') {
            return $this->ui->redirect('/dev/overview');
        }

        return $this->ui->renderAuth('login.html', [
            'title' => 'Connexion développeur',
            'flash' => $this->ui->flashFromRequest($request),
        ]);
    }

    public function login(Request $request): Response
    {
        $data = FormData::fromRequest($request);
        $password = $data['password'] ?? '';

        if ($this->devPassword !== '' && !hash_equals($this->devPassword, $password)) {
            $response = $this->ui->redirect('/dev');

            return $this->ui->withFlash($response, 'Mot de passe incorrect.');
        }

        $response = $this->ui->redirect('/dev/overview');

        return $this->responses->withCookie($response, Cookie::create('capsule_dev', '1', [
            'path' => '/',
            'httpOnly' => true,
            'sameSite' => 'Strict',
            'maxAge' => 86400 * 7,
        ]));
    }

    public function logout(Request $request): Response
    {
        $response = $this->ui->redirect('/dev');

        return $this->responses->withCookie($response, Cookie::create('capsule_dev', '', [
            'path' => '/',
            'httpOnly' => true,
            'sameSite' => 'Strict',
            'maxAge' => 0,
        ]));
    }
}
