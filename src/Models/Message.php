<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models;

/**
 * Message.
 */
final readonly class Message extends AbstractModel
{
    /**
     * @param MessageBody $body Body of created message. Text + attachments.
     */
    public function __construct(
        public MessageBody $body,
    ) {
    }
}
