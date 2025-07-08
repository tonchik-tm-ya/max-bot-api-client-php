<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models;

use BushlanovDev\MaxMessengerBot\Enums\UpdateType;

final readonly class SubscriptionRequestBody extends AbstractModel
{
    /**
     * @param string $url URL webhook.
     * @param string|null $secret Secret key for verifying the authenticity of requests.
     * @param UpdateType[]|null $update_types List of update types.
     * @param string|null $version Version of the API.
     */
    public function __construct(
        public string $url,
        public ?string $secret = null,
        public ?array $update_types = null,
        public ?string $version = null,
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
                fn(string $typeValue): UpdateType => UpdateType::from($typeValue),
                $data['update_types'],
            );
        }

        return new static(
            (string)$data['url'],
            $data['secret'] ?? null,
            $updateTypes,
            $data['version'] ?? null,
        );
    }
}
