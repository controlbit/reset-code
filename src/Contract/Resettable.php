<?php
declare(strict_types=1);

namespace Choks\ResetCode\Contract;

interface Resettable
{
    public function getIdentifier(): string|int;
}