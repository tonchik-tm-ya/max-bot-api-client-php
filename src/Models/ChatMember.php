<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models;

use BushlanovDev\MaxMessengerBot\Attributes\ArrayOf;
use BushlanovDev\MaxMessengerBot\Enums\ChatAdminPermission;

/**
 * Represents a member of a chat, including their user information and chat-specific status.
 */
final readonly class ChatMember extends AbstractModel
{
    /**
     * @param int $userId User's identifier.
     * @param string $firstName User's first name.
     * @param string|null $lastName User's last name.
     * @param string|null $username User's public username.
     * @param bool $isBot True if the user is a bot.
     * @param int $lastActivityTime Time of the user's last activity in Max.
     * @param string|null $description User's profile description.
     * @param string|null $avatarUrl URL of the user's avatar.
     * @param string|null $fullAvatarUrl URL of the user's full-sized avatar.
     * @param int $lastAccessTime The time the user last accessed the chat.
     * @param bool $isOwner True if this member is the owner of the chat.
     * @param bool $isAdmin True if this member is an administrator of the chat.
     * @param int $joinTime The time the user joined the chat.
     * @param ChatAdminPermission[]|null $permissions A list of permissions if the member is an admin, otherwise null.
     */
    public function __construct(
        public int $userId,
        public string $firstName,
        public ?string $lastName,
        public ?string $username,
        public bool $isBot,
        public int $lastActivityTime,
        public ?string $description,
        public ?string $avatarUrl,
        public ?string $fullAvatarUrl,
        public int $lastAccessTime,
        public bool $isOwner,
        public bool $isAdmin,
        public int $joinTime,
        #[ArrayOf(ChatAdminPermission::class)]
        public ?array $permissions,
    ) {
    }
}
