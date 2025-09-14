<?php
declare(strict_types=1);

namespace ControlBit\ResetCode\Doctrine;

use ControlBit\DoctrineUtils\Types\EntityType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;

final class Schema
{
    public const TABLE_PREFIX      = 'reset_code';
    public const COLUMN_SUBJECT    = 'subject';
    public const COLUMN_CODE       = 'code';
    public const COLUMN_EXPIRE_AT  = 'expire_at';
    public const COLUMN_CREATED_AT = 'created_at';

    public function __construct(
        private readonly int    $codeSize,
        private readonly string $tableName,
    ) {
    }

    public function postGenerateSchema(GenerateSchemaEventArgs $eventArgs): void
    {
        $schema = $eventArgs->getSchema();
        $table  = $schema->createTable($this->tableName);

        $table->addColumn(self::COLUMN_SUBJECT, EntityType::NAME);
        $table->addColumn(self::COLUMN_CODE, Types::STRING, ['length' => $this->codeSize]);
        $table->addColumn(self::COLUMN_EXPIRE_AT, Types::DATETIME_IMMUTABLE);
        $table->addColumn(self::COLUMN_CREATED_AT, Types::DATETIME_IMMUTABLE);
    }
}