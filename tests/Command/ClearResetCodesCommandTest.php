<?php
declare(strict_types=1);

namespace Choks\ResetCode\Tests\Command;

use Choks\ResetCode\Tests\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ClearResetCodesCommandTest extends KernelTestCase
{
    public function testExecute(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel); // @phpstan-ignore-line

        $command = $application->find('reset-code:clear');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();
    }
}