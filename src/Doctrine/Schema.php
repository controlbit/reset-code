<?php
declare(strict_types=1);

namespace Choks\ResetCode\Doctrine;

use Choks\DoctrineUtils\Types\EntityType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;

final class Schema
{
    public const TABLE_PREFIX = 'reset_code';

    public function __construct(
        private readonly int    $codeSize,
        private readonly string $tableName,
    ) {
    }

    public function postGenerateSchema(GenerateSchemaEventArgs $eventArgs)
    {
        $schema = $eventArgs->getSchema();
        $table  = $schema->createTable($this->tableName);

        $table->addColumn('subject', EntityType::NAME);
        $table->addColumn('code', Types::STRING, ['length' => $this->codeSize]);
        $table->addColumn('expire_at', Types::DATETIME_IMMUTABLE);
        $table->addColumn('created_at', Types::DATETIME_IMMUTABLE);
    }
}