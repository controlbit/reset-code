<?php

declare(strict_types=1);

namespace Choks\ResetCode\Service;

use Choks\DoctrineUtils\Types\EntityType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

final class ResetCodeManager
{
    public function __construct(
        private readonly Connection $connection,
        private readonly string     $tableName,
        private readonly int        $length,
        private readonly int        $ttl,
        private readonly int        $timeoutToClearOldest,
        private readonly bool       $allowSubjectDuplicates,
    ) {
    }

    public function createResetCode(object $subject): string
    {
        if (false === $this->allowSubjectDuplicates) {
            $this->removeWithSubject($subject);
        }

        $startTime = microtime(true);

        do {
            if ((microtime(true) - $startTime) * 1000 > $this->timeoutToClearOldest) {
                $this->clearOldest();
                $startTime = microtime(true);
            }

            $code = $this->generateResetCode($this->length);
        } while ($this->codeExists($code));

        $this->insert($subject, $code);

        return $code;
    }

    public function codeExists(string|int $code): bool
    {
        $result = $this->connection->executeQuery(
            \sprintf('SELECT 1 FROM %s WHERE code = :code ', $this->tableName),
            ['code' => (string)$code]
        );

        return $result->rowCount() > 0;
    }

    public function getSubjectByCode(string|int $code): ?object
    {
        $result = $this->connection->executeQuery(
            \sprintf('SELECT subject FROM %s WHERE code = :code ', $this->tableName),
            ['code' => (string)$code]
        );

        $subject = $result->fetchOne();

        if (false === $subject) {
            return null;
        }

        // TODO: Find out how to get DB Platform for subject!
        // Currently it uses same as reset-code connection! Which is bad...
        return Type::getType(EntityType::NAME)
                   ->convertToPHPValue($subject, $this->connection->getDatabasePlatform())
        ;
    }

    public function isExpired(string|int $code): bool
    {
        $result = $this->connection->executeQuery(
            \sprintf('SELECT 1 FROM %s WHERE code = :code AND NOW() < expire_at ', $this->tableName),
            ['code' => (string)$code]
        );

        return $result->rowCount() === 0;
    }

    public function subjectExists(object $subject): bool
    {
        $result = $this->connection->executeQuery(
            \sprintf('SELECT 1 FROM %s WHERE subject = :subject ', $this->tableName),
            ['subject' => $subject],
            ['subject' => EntityType::NAME]
        );

        return $result->rowCount() > 0;
    }

    public function removeWithSubject(object $subject): void
    {
        $this->connection->executeStatement(
            \sprintf('DELETE FROM %s WHERE subject = :subject ', $this->tableName),
            ['subject' => $subject],
            ['subject' => EntityType::NAME]
        );
    }

    public function clearExpired(): void
    {
        $this->connection->executeStatement(
            \sprintf('DELETE FROM %s WHERE expire_at < NOW() ', $this->tableName),
        );
    }

    public function clearOldest(): void
    {
        $this->connection->executeStatement(
            \sprintf('DELETE FROM %s ORDER BY created_at DESC LIMIT 1 ', $this->tableName),
        );
    }

    public function clearAll(): void
    {
        /**
         * It has to be DELETE FROM Instead of TRUNCATE.
         * @see https://github.com/doctrine/data-fixtures/issues/448
         */
        $this->connection->executeStatement(
            \sprintf('DELETE FROM %s', $this->tableName),
        );
    }

    public function totalCount(): int
    {
        $result = $this->connection->executeQuery(
            \sprintf('SELECT COUNT(*) FROM %s', $this->tableName)
        );

        return (int)$result->fetchOne();
    }

    public function totalExpiredCount(): int
    {
        $result = $this->connection->executeQuery(
            \sprintf('SELECT COUNT(*) FROM %s WHERE expire_at <= NOW()', $this->tableName)
        );

        return (int)$result->fetchOne();
    }

    private function generateResetCode(int $length): string
    {
        $memorableDigits = \array_map(static fn($digit) => \random_int(0, 9), \range(0, \ceil($length / 2)));
        $code            = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $memorableDigits[\array_rand($memorableDigits)];
        }

        return $code;
    }

    private function insert(object $subject, string|int $code): void
    {
        $this->connection->executeStatement(
            \sprintf(
                'INSERT INTO %s 
                        (subject, code, expire_at, created_at)
                        VALUES
                        (:subject, :code, :expire_at, :created_at)',
                $this->tableName
            ),
            [
                'subject'    => $subject,
                'code'       => (string)$code,
                'expire_at'  => new \DateTimeImmutable('now + '.$this->ttl.' seconds'),
                'created_at' => new \DateTimeImmutable(),
            ],
            [
                'subject'    => EntityType::NAME,
                'expire_at'  => Types::DATETIME_IMMUTABLE,
                'created_at' => Types::DATETIME_IMMUTABLE,
            ]
        );
    }
}
