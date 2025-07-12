<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models;

/**
 * Body of created message. Text + attachments.
 */
final readonly class MessageBody extends AbstractModel
{
    /**
     * @param string $mid Unique identifier of message.
     * @param int $seq Sequence identifier of message in chat.
     * @param string|null $text Message text.
     */
    public function __construct(
        public string $mid,
        public int $seq,
        public ?string $text,
    ) {
    }
}
