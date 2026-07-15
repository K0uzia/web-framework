<?php

declare(strict_types=1);

namespace Capsule\Section\Signup;

use Capsule\Section\AbstractSectionTypeHandler;

final class SignupSectionHandler extends AbstractSectionTypeHandler
{
    public const TYPE = 'signup';

    protected string $styleClass = SignupStyle::class;
    protected string $rendererClass = SignupVariantRenderer::class;

    public function jsModules(string $variant): array
    {
        return ['sections/auth-switch.js'];
    }
}
