<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models;

/**
 * Simple response to request.
 */
final readonly class ResultModel extends AbstractModel
{
    /**
     * @param bool $success true if request was successful, false otherwise.
     * @param string|null $message Explanatory message if the result was not successful.
     */
    public function __construct(
        public bool $success,
        public ?string $message,
    ) {
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $data): static
    {
        return new static(
            (bool)$data['success'],
            $data['message'] ?? null,
        );
    }
}
