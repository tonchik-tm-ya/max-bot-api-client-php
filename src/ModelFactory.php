<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot;

use BushlanovDev\MaxMessengerBot\Enums\AttachmentType;
use BushlanovDev\MaxMessengerBot\Enums\MarkupType;
use BushlanovDev\MaxMessengerBot\Enums\UpdateType;
use BushlanovDev\MaxMessengerBot\Models\Attachments\AbstractAttachment;
use BushlanovDev\MaxMessengerBot\Models\Attachments\DataAttachment;
use BushlanovDev\MaxMessengerBot\Models\Attachments\ShareAttachment;
use BushlanovDev\MaxMessengerBot\Models\BotInfo;
use BushlanovDev\MaxMessengerBot\Models\Chat;
use BushlanovDev\MaxMessengerBot\Models\ChatList;
use BushlanovDev\MaxMessengerBot\Models\ChatMember;
use BushlanovDev\MaxMessengerBot\Models\ChatMembersList;
use BushlanovDev\MaxMessengerBot\Models\Markup\AbstractMarkup;
use BushlanovDev\MaxMessengerBot\Models\Markup\EmphasizedMarkup;
use BushlanovDev\MaxMessengerBot\Models\Markup\HeadingMarkup;
use BushlanovDev\MaxMessengerBot\Models\Markup\HighlightedMarkup;
use BushlanovDev\MaxMessengerBot\Models\Markup\LinkMarkup;
use BushlanovDev\MaxMessengerBot\Models\Markup\MonospacedMarkup;
use BushlanovDev\MaxMessengerBot\Models\Markup\StrikethroughMarkup;
use BushlanovDev\MaxMessengerBot\Models\Markup\StrongMarkup;
use BushlanovDev\MaxMessengerBot\Models\Markup\UnderlineMarkup;
use BushlanovDev\MaxMessengerBot\Models\Markup\UserMentionMarkup;
use BushlanovDev\MaxMessengerBot\Models\Message;
use BushlanovDev\MaxMessengerBot\Models\Result;
use BushlanovDev\MaxMessengerBot\Models\Subscription;
use BushlanovDev\MaxMessengerBot\Models\UpdateList;
use BushlanovDev\MaxMessengerBot\Models\Updates\AbstractUpdate;
use BushlanovDev\MaxMessengerBot\Models\Updates\BotAddedToChatUpdate;
use BushlanovDev\MaxMessengerBot\Models\Updates\BotRemovedFromChatUpdate;
use BushlanovDev\MaxMessengerBot\Models\Updates\BotStartedUpdate;
use BushlanovDev\MaxMessengerBot\Models\Updates\ChatTitleChangedUpdate;
use BushlanovDev\MaxMessengerBot\Models\Updates\MessageCallbackUpdate;
use BushlanovDev\MaxMessengerBot\Models\Updates\MessageChatCreatedUpdate;
use BushlanovDev\MaxMessengerBot\Models\Updates\MessageCreatedUpdate;
use BushlanovDev\MaxMessengerBot\Models\Updates\MessageEditedUpdate;
use BushlanovDev\MaxMessengerBot\Models\Updates\MessageRemovedUpdate;
use BushlanovDev\MaxMessengerBot\Models\Updates\UserAddedToChatUpdate;
use BushlanovDev\MaxMessengerBot\Models\Updates\UserRemovedFromChatUpdate;
use BushlanovDev\MaxMessengerBot\Models\UploadEndpoint;
use BushlanovDev\MaxMessengerBot\Models\VideoAttachmentDetails;
use LogicException;
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
        if (isset($data['body']['attachments']) && is_array($data['body']['attachments'])) {
            $data['body']['attachments'] = array_map(
                [$this, 'createAttachment'],
                $data['body']['attachments'],
            );
        }

        if (isset($data['body']['markup']) && is_array($data['body']['markup'])) {
            $data['body']['markup'] = array_map(
                [$this, 'createMarkupElement'],
                $data['body']['markup'],
            );
        }

        return Message::fromArray($data);
    }

    /**
     * Creates a specific Attachment model based on the 'type' field.
     *
     * @param array<string, mixed> $data
     *
     * @return AbstractAttachment
     * @throws ReflectionException
     */
    public function createAttachment(array $data): AbstractAttachment
    {
        return match (AttachmentType::tryFrom($data['type'] ?? '')) {
            AttachmentType::Data => DataAttachment::fromArray($data),
            AttachmentType::Share => ShareAttachment::fromArray($data),
            default => throw new LogicException(
                'Unknown or unsupported Attachment type: ' . ($data['type'] ?? 'none')
            ),
        };
    }

    /**
     * List of messages.
     *
     * @param array<string, mixed> $data
     *
     * @return Message[]
     */
    public function createMessages(array $data): array
    {
        return isset($data['messages']) && is_array($data['messages'])
            ? array_map([$this, 'createMessage'], $data['messages'])
            : [];
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

    /**
     * Creates a list of updates from a raw API response.
     *
     * @param array<string, mixed> $data Raw response data.
     *
     * @return UpdateList
     * @throws ReflectionException
     * @throws LogicException
     */
    public function createUpdateList(array $data): UpdateList
    {
        $updateObjects = [];
        if (isset($data['updates']) && is_array($data['updates'])) {
            foreach ($data['updates'] as $updateData) {
                // Here we delegate the creation of a specific update to another factory method
                $updateObjects[] = $this->createUpdate($updateData);
            }
        }

        return new UpdateList(
            $updateObjects,
            $data['marker'] ? (int)$data['marker'] : null,
        );
    }

    /**
     * Creates a specific Update model based on the 'update_type' field.
     *
     * @param array<string, mixed> $data Raw data for a single update.
     *
     * @return AbstractUpdate
     * @throws ReflectionException
     * @throws LogicException
     */
    public function createUpdate(array $data): AbstractUpdate
    {
        return match (UpdateType::tryFrom($data['update_type'] ?? '')) {
            UpdateType::MessageCreated => MessageCreatedUpdate::fromArray($data),
            UpdateType::MessageCallback => MessageCallbackUpdate::fromArray($data),
            UpdateType::MessageEdited => MessageEditedUpdate::fromArray($data),
            UpdateType::MessageRemoved => MessageRemovedUpdate::fromArray($data),
            UpdateType::BotAdded => BotAddedToChatUpdate::fromArray($data),
            UpdateType::BotRemoved => BotRemovedFromChatUpdate::fromArray($data),
            UpdateType::UserAdded => UserAddedToChatUpdate::fromArray($data),
            UpdateType::UserRemoved => UserRemovedFromChatUpdate::fromArray($data),
            UpdateType::BotStarted => BotStartedUpdate::fromArray($data),
            UpdateType::ChatTitleChanged => ChatTitleChangedUpdate::fromArray($data),
            UpdateType::MessageChatCreated => MessageChatCreatedUpdate::fromArray($data),
            default => throw new LogicException(
                'Unknown or unsupported update type received: ' . ($data['update_type'] ?? 'none')
            ),
        };
    }

    /**
     * Information about chat list.
     *
     * @param array<string, mixed> $data
     *
     * @return ChatList
     * @throws ReflectionException
     */
    public function createChatList(array $data): ChatList
    {
        return ChatList::fromArray($data);
    }

    /**
     * Creates a ChatMember object from raw API data.
     *
     * @param array<string, mixed> $data
     *
     * @return ChatMember
     * @throws ReflectionException
     */
    public function createChatMember(array $data): ChatMember
    {
        return ChatMember::fromArray($data);
    }

    /**
     * Creates a ChatMembersList object from raw API data.
     *
     * @param array<string, mixed> $data
     *
     * @return ChatMembersList
     * @throws ReflectionException
     */
    public function createChatMembersList(array $data): ChatMembersList
    {
        return ChatMembersList::fromArray($data);
    }

    /**
     * Creates a VideoAttachmentDetails object from raw API data.
     *
     * @param array<string, mixed> $data
     *
     * @return VideoAttachmentDetails
     * @throws ReflectionException
     */
    public function createVideoAttachmentDetails(array $data): VideoAttachmentDetails
    {
        return VideoAttachmentDetails::fromArray($data);
    }

    /**
     * Creates a specific Markup model based on the 'type' field.
     *
     * @param array<string, mixed> $data
     *
     * @return AbstractMarkup
     * @throws ReflectionException
     */
    public function createMarkupElement(array $data): AbstractMarkup
    {
        return match (MarkupType::tryFrom($data['type'] ?? '')) {
            MarkupType::Strong => StrongMarkup::fromArray($data),
            MarkupType::Emphasized => EmphasizedMarkup::fromArray($data),
            MarkupType::Monospaced => MonospacedMarkup::fromArray($data),
            MarkupType::Strikethrough => StrikethroughMarkup::fromArray($data),
            MarkupType::Underline => UnderlineMarkup::fromArray($data),
            MarkupType::Heading => HeadingMarkup::fromArray($data),
            MarkupType::Highlighted => HighlightedMarkup::fromArray($data),
            MarkupType::Link => LinkMarkup::fromArray($data),
            MarkupType::UserMention => UserMentionMarkup::fromArray($data),
            default => throw new LogicException(
                'Unknown or unsupported markup type: ' . ($data['type'] ?? 'none')
            ),
        };
    }
}
