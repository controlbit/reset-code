<?php
declare(strict_types=1);

namespace Choks\ResetCode\Tests\Service;

use Choks\ResetCode\Contract\Resettable;
use Choks\ResetCode\Service\ResetCodeManager;
use Choks\ResetCode\Tests\KernelTestCase;
use Choks\ResetCode\Tests\Resources\App\Entity\SampleEntity;
use Doctrine\ORM\EntityManagerInterface;

final class ManagerTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testGeneratedCode(): void
    {
        $manager = self::getContainer()->get('reset_code.alpha');
        $subject = $this->entityManager->find(SampleEntity::class, 10);
        $code    = $manager->createResetCode($subject);

        self::assertTrue($manager->subjectExists($subject));
        self::assertTrue($manager->codeExists($code));
        self::assertEquals(6, \strlen($code));
    }

    public function testExpiration(): void
    {
        $manager = self::getContainer()->get('reset_code.alpha');
        $subject = $this->entityManager->find(SampleEntity::class, 10);
        $code    = $manager->createResetCode($subject);

        self::assertTrue($manager->codeExists($code));
        self::assertTrue($manager->subjectExists($subject));
        self::assertFalse($manager->isExpired($code));
        self::assertEquals(0, $manager->totalExpiredCount());

        \sleep(2); // In kernel test case, alpha reset code manager TTL is set to 1 sec. We wait little more...

        self::assertTrue($manager->codeExists($code));
        self::assertTrue($manager->isExpired($code));
        self::assertEquals(1, $manager->totalExpiredCount());

        $manager->clearExpired();

        self::assertFalse($manager->codeExists($code));
        self::assertFalse($manager->subjectExists($subject));
        self::assertTrue($manager->isExpired($code)); // Expired or does not exist should behave same
        self::assertEquals(0, $manager->totalExpiredCount());
    }

    public function testClearingOldest(): void
    {
        $manager = self::getContainer()->get('reset_code.alpha');
        $subject = $this->entityManager->find(SampleEntity::class, 10);
        $code    = $manager->createResetCode($subject);

        self::assertTrue($manager->codeExists($code));

        $manager->clearOldest();

        self::assertFalse($manager->codeExists($code));
    }

    public function testDdos(): void
    {
        /** @var ResetCodeManager $manager */
        $manager = self::getContainer()->get('reset_code.ddos');
        $subject = $this->entityManager->find(SampleEntity::class, 10);

        $iterations = (10 ** 1) + 1;

        for ($i = 0; $i < $iterations; $i++) {
            $manager->createResetCode($subject);
        }

        self::assertEquals(10, $manager->totalCount());

        $manager->clearAll();
    }

    public function testSubjectRetrieval(): void
    {
        $manager = self::getContainer()->get('reset_code.alpha');
        $subject = $this->entityManager->find(SampleEntity::class, 10);
        $code    = $manager->createResetCode($subject);
        $id      = $subject->getIdentifier();

        unset($subject);

        /** @var Resettable $subject */
        $subject = $manager->getSubjectByCode($code);
        self::assertEquals($id, $subject->getIdentifier());
    }
}