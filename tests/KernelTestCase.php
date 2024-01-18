<?php
declare(strict_types=1);

namespace Choks\ResetCode\Tests;

use Choks\ResetCode\Tests\Resources\App\ResetCodeTestKernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class KernelTestCase extends WebTestCase
{
    protected static function getKernelClass(): string
    {
        return ResetCodeTestKernel::class;
    }

}