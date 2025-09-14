<?php
declare(strict_types=1);

namespace ControlBit\ResetCode\Contract;

interface Resettable
{
    public function getIdentifier(): string|int;
}