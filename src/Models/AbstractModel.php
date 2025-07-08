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
        return array_map(function ($value) {
            return $this->convertValue($value);
        }, get_object_vars($this));
    }

    private function convertValue(mixed $value): mixed
    {
        if ($value instanceof AbstractModel) {
            return $value->toArray();
        }

        if (is_array($value)) {
            return array_map([$this, 'convertValue'], $value);
        }

        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        return $value;
    }
}
