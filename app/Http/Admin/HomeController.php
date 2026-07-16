<?php

declare(strict_types=1);

namespace App\Http\Admin;

use Capsule\AdminDashboard;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;

final class HomeController
{
    public function __construct(
        private readonly AdminDashboard $ui,
    ) {
    }

    public function index(Request $request): Response
    {
        return $this->ui->redirect('/admin/pages');
    }
}
