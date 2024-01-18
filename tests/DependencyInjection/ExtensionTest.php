<?php
declare(strict_types=1);

namespace Choks\ResetCode\Tests\DependencyInjection;

use Choks\ResetCode\Service\ResetCodeManager;
use Choks\ResetCode\Tests\KernelTestCase;

final class ExtensionTest extends KernelTestCase
{
    public function testAlpha(): void
    {
        $subject = self::getContainer()->get('reset_code.alpha');
        self::assertInstanceOf(ResetCodeManager::class, $subject);
    }

    public function testBetaAlias(): void
    {
        $subject = self::getContainer()->get('reset_code.two');
        self::assertInstanceOf(ResetCodeManager::class, $subject);
    }
}