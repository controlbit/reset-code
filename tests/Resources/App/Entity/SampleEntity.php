<?php

declare(strict_types=1);

namespace ControlBit\ResetCode\Tests\Resources\App\Entity;

use ControlBit\ResetCode\Contract\Resettable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class SampleEntity implements Resettable
{
    #[ORM\Id]
    #[ORM\Column]
    public ?int $id = null;

    public function __construct(?int $id = null)
    {
        $this->id = $id;
    }

    public function getIdentifier(): string|int
    {
        return $this->id;
    }
}