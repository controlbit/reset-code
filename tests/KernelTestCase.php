<?php
declare(strict_types=1);

namespace ControlBit\ResetCode\Tests;

use ControlBit\ResetCode\Tests\Resources\App\ResetCodeTestKernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class KernelTestCase extends WebTestCase
{
    protected static function getKernelClass(): string
    {
        return ResetCodeTestKernel::class;
    }

}