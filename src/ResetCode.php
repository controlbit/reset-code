<?php

declare(strict_types=1);

namespace Choks\ResetCode;

use Choks\ResetCode\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class ResetCode extends Bundle
{
    public function getNamespace(): string
    {
        return 'reset_code';
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new Extension();
    }
}