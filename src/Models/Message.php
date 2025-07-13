<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models;

/**
 * Message.
 */
final readonly class Message extends AbstractModel
{
    /**
     * @param int $timestamp Unix-time when message was created.
     * @param MessageBody $body Body of created message. Text + attachments.
     * @param Recipient $recipient Message recipient. Could be user or chat.
     */
    public function __construct(
        public int $timestamp,
        public MessageBody $body,
        public Recipient $recipient,
    ) {
    }
}
