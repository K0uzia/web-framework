<?php

declare(strict_types=1);

namespace App\Http\Admin;

use Capsule\AdminDashboard;
use Capsule\Http\Factory\ResponseFactory;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\Http\Support\Cookie;
use Capsule\Http\Support\FormData;

final class AuthController
{
    public function __construct(
        private readonly AdminDashboard $ui,
        private readonly ResponseFactory $responses,
        private readonly string $clientPassword,
    ) {
    }

    public function loginForm(Request $request): Response
    {
        if (($request->cookies['capsule_client'] ?? '') === '1') {
            return $this->ui->redirect('/admin/pages');
        }

        return $this->ui->renderAuth('login.html', [
            'title' => 'Connexion',
            'flash' => $this->ui->flashFromRequest($request),
        ]);
    }

    public function login(Request $request): Response
    {
        $data = FormData::fromRequest($request);
        $password = $data['password'] ?? '';

        if ($this->clientPassword !== '' && !hash_equals($this->clientPassword, $password)) {
            return $this->ui->withFlash($this->ui->redirect('/admin'), 'Mot de passe incorrect.');
        }

        $response = $this->ui->redirect('/admin/pages');

        return $this->responses->withCookie($response, Cookie::create('capsule_client', '1', [
            'path' => '/',
            'httpOnly' => true,
            'sameSite' => 'Strict',
            'maxAge' => 86400 * 7,
        ]));
    }

    public function logout(Request $request): Response
    {
        $response = $this->ui->redirect('/admin');

        return $this->responses->withCookie($response, Cookie::create('capsule_client', '', [
            'path' => '/',
            'httpOnly' => true,
            'sameSite' => 'Strict',
            'maxAge' => 0,
        ]));
    }
}
