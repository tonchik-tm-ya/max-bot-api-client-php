<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models;

use BushlanovDev\MaxMessengerBot\Enums\UpdateType;

/**
 * Information about webhook subscriptions.
 */
final readonly class Subscription extends AbstractModel
{
    /**
     * @param string $url URL webhook.
     * @param int $time Unix-time of creating a subscription.
     * @param UpdateType[]|null $update_types List of update types.
     * @param string|null $version Version of the API.
     */
    public function __construct(
        public string $url,
        public int $time,
        public ?array $update_types,
        public ?string $version,
    ) {
    }

    /**
     * @inheritdoc
     */
    public static function fromArray(array $data): static
    {
        $updateTypes = null;
        if (isset($data['update_types']) && is_array($data['update_types'])) {
            $updateTypes = array_map(
                fn (string $typeValue): UpdateType => UpdateType::from($typeValue),
                $data['update_types'],
            );
        }

        return new static(
            (string)$data['url'],
            (int)$data['time'],
            $updateTypes,
            $data['version'] ?? null,
        );
    }
}
