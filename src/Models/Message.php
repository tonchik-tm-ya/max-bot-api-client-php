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
     * @param Sender|null $sender User who sent this message. Can be null if message has been posted on behalf of a channel.
     * @param string|null $url Message public URL. Can be null for dialogs or non-public chats/channels.
     */
    public function __construct(
        public int $timestamp,
        public MessageBody $body,
        public Recipient $recipient,
        public ?Sender $sender,
        public ?string $url,
    ) {
    }
}
