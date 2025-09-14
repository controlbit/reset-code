<?php
declare(strict_types=1);

namespace ControlBit\ResetCode\Tests\Command;

use ControlBit\ResetCode\Command\ClearResetCodesCommand;
use ControlBit\ResetCode\Doctrine\Schema;
use ControlBit\ResetCode\Service\ResetCodeManager;
use ControlBit\ResetCode\Tests\KernelTestCase;
use ControlBit\ResetCode\Tests\Resources\App\Entity\SampleEntity;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ClearResetCodesCommandTest extends KernelTestCase
{
    private const TABLE_NAME = 'reset_code_alpha';

    private Connection $connection;

    private ResetCodeManager $resetCodeManager;

    private EntityManagerInterface $entityManager;

    private SampleEntity $sampleEntity2;
    private SampleEntity $sampleEntity3;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection       = self::getContainer()->get(Connection::class);
        $this->resetCodeManager = self::getContainer()->get('reset_code.alpha');
        $this->entityManager    = self::getContainer()->get(EntityManagerInterface::class);

        $this->sampleEntity2    = new SampleEntity(11);
        $this->sampleEntity3    = new SampleEntity(12);

        $this->entityManager->persist($this->sampleEntity2);
        $this->entityManager->persist($this->sampleEntity3);
        $this->entityManager->flush();
    }


    public function testExecute(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel); // @phpstan-ignore-line

        $command       = $application->find('reset-code:clear');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();
    }

    public function testExecuteClearExpiredCodes(): void
    {
        self::bootKernel();

        /** @var SampleEntity $subject */
        $subject = $this->entityManager->find(SampleEntity::class, 10);
        $code    = $this->resetCodeManager->createResetCode($subject);

        $this->connection->executeStatement(
            \sprintf(
                "UPDATE %s SET %s = :expireDate WHERE %s = :code",
                self::TABLE_NAME,
                Schema::COLUMN_EXPIRE_AT,
                Schema::COLUMN_CODE,

            ),
            [
                'expireDate' => (new \DateTimeImmutable('-1 day'))->format('Y-m-d H:i:s'),
                'code'       => $code,
            ]
        );

        self::assertEquals(1, $this->getResetCodeCount());

        $application = new Application(self::$kernel); // @phpstan-ignore-line

        $command       = $application->find('reset-code:clear');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        self::assertEquals(0, $this->getResetCodeCount());
    }

    public function testExecuteClearAllCodes(): void
    {
        self::bootKernel();

        /** @var SampleEntity $subject */
        $subject = $this->entityManager->find(SampleEntity::class, 10);
        $code    = $this->resetCodeManager->createResetCode($subject);

        $this->connection->executeStatement(
            \sprintf(
                "UPDATE %s SET %s = :expireDate WHERE %s = :code",
                self::TABLE_NAME,
                Schema::COLUMN_EXPIRE_AT,
                Schema::COLUMN_CODE,

            ),
            [
                'expireDate' => (new \DateTimeImmutable('-1 day'))->format('Y-m-d H:i:s'),
                'code'       => $code,
            ]
        );

        self::assertEquals(1, $this->getResetCodeCount());

        $application = new Application(self::$kernel); // @phpstan-ignore-line

        $command       = $application->find('reset-code:clear');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'which' => ClearResetCodesCommand::ALL,
            ]
        );

        self::assertEquals(0, $this->getResetCodeCount());
    }

    private function getResetCodeCount(): int
    {
        $result = $this->connection->executeQuery(\sprintf('SELECT COUNT(*) FROM %s', self::TABLE_NAME));

        return (int)$result->fetchFirstColumn()[0];
    }
}