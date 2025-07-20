<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot;

use BushlanovDev\MaxMessengerBot\Models\BotInfo;
use BushlanovDev\MaxMessengerBot\Models\Chat;
use BushlanovDev\MaxMessengerBot\Models\Message;
use BushlanovDev\MaxMessengerBot\Models\Result;
use BushlanovDev\MaxMessengerBot\Models\Subscription;
use BushlanovDev\MaxMessengerBot\Models\UploadEndpoint;
use ReflectionException;

/**
 * Creates DTOs from raw associative arrays returned by the API client.
 */
class ModelFactory
{
    /**
     * Simple response to request.
     *
     * @param array<string, mixed> $data
     *
     * @return Result
     * @throws ReflectionException
     */
    public function createResult(array $data): Result
    {
        return Result::fromArray($data);
    }

    /**
     * Information about the current bot.
     *
     * @param array<string, mixed> $data
     *
     * @return BotInfo
     * @throws ReflectionException
     */
    public function createBotInfo(array $data): BotInfo
    {
        return BotInfo::fromArray($data);
    }

    /**
     * Information about webhook subscription.
     *
     * @param array<string, mixed> $data
     *
     * @return Subscription
     * @throws ReflectionException
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
     * @throws ReflectionException
     */
    public function createSubscriptions(array $data): array
    {
        return isset($data['subscriptions']) && is_array($data['subscriptions'])
            ? array_map([$this, 'createSubscription'], $data['subscriptions'])
            : [];
    }

    /**
     * Message.
     *
     * @param array<string, mixed> $data
     *
     * @return Message
     * @throws ReflectionException
     */
    public function createMessage(array $data): Message
    {
        return Message::fromArray($data);
    }

    /**
     * Endpoint you should upload to your binaries.
     *
     * @param array<string, mixed> $data
     *
     * @return UploadEndpoint
     * @throws ReflectionException
     */
    public function createUploadEndpoint(array $data): UploadEndpoint
    {
        return UploadEndpoint::fromArray($data);
    }

    /**
     * Chat information.
     *
     * @param array<string, mixed> $data
     *
     * @return Chat
     * @throws ReflectionException
     */
    public function createChat(array $data): Chat
    {
        return Chat::fromArray($data);
    }
}
