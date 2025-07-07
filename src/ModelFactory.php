<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot;

use BushlanovDev\MaxMessengerBot\Models\BotCommand;
use BushlanovDev\MaxMessengerBot\Models\BotInfo;
use BushlanovDev\MaxMessengerBot\Models\Subscription;

/**
 * Creates DTOs from raw associative arrays returned by the API client.
 */
class ModelFactory
{
    /**
     * Information about the current bot.
     *
     * @param array<string, mixed> $data
     *
     * @return BotInfo
     */
    public function createBotInfo(array $data): BotInfo
    {
        $data['commands'] = isset($data['commands']) && is_array($data['commands'])
            ? array_map([BotCommand::class, 'fromArray'], $data['commands']) : null;

        return BotInfo::fromArray($data);
    }

    /**
     * Information about webhook subscription.
     *
     * @param array<string, mixed> $data
     *
     * @return Subscription
     */
    public function createSubscription(array $data): Subscription
    {
        return Subscription::fromArray($data);
    }

    /**
     * List of all active webhook subscriptions.
     *
     * @param array<string, mixed> $data
     *
     * @return Subscription[]
     */
    public function createSubscriptions(array $data): array
    {
        return isset($data['subscriptions']) && is_array($data['subscriptions'])
            ? array_map([$this, 'createSubscription'], $data['subscriptions'])
            : [];
    }
}
