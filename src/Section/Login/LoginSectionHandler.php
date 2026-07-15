<?php

declare(strict_types=1);

namespace Capsule\Section\Login;

use Capsule\Section\AbstractSectionTypeHandler;

final class LoginSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'login';

    protected string $styleClass = LoginStyle::class;
    protected string $rendererClass = LoginVariantRenderer::class;

    public function jsModules(string $variant): array
    {
        return ['sections/auth-switch.js'];
    }
}
