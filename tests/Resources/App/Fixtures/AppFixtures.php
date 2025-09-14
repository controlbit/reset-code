<?php
declare(strict_types=1);

namespace ControlBit\ResetCode\Tests\Resources\App\Fixtures;

use ControlBit\ResetCode\Tests\Resources\App\Entity\SampleEntity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $sampleEntity = new SampleEntity(10);

        $manager->persist($sampleEntity);
        $manager->flush();
    }
}