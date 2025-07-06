<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot;

use BushlanovDev\MaxMessengerBot\Models\BotCommand;
use BushlanovDev\MaxMessengerBot\Models\BotInfo;

/**
 * Creates DTOs from raw associative arrays returned by the API client.
 */
class ModelFactory
{
    /**
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
}
