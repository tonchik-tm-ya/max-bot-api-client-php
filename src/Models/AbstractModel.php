<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models;

abstract readonly class AbstractModel
{
    /**
     * @param array<string, mixed> $data
     */
    abstract public static function fromArray(array $data): static;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
