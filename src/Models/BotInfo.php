<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models;

/**
 * Information about the current bot, which is identified using an access token.
 */
final readonly class BotInfo extends AbstractModel
{
    /**
     * @param int $user_id ID user
     * @param string $first_name User display name
     * @param string|null $last_name User's display last name
     * @param string|null $username Unique public name of the user, may be null if the user is not available or no name is set
     * @param bool $is_bot Is the user a bot
     * @param int $last_activity_time User last activity time in MAX (Unix time in milliseconds). May be irrelevant if the user has disabled the "online" status in the settings
     * @param string|null $description User description, may be null if the user has not filled it in (up to 16000 characters)
     * @param string|null $avatar_url Avatar URL
     * @param string|null $full_avatar_url Larger Avatar URL
     * @param BotCommand[]|null $commands Commands supported by the bot (up to 32 elements)
     */
    public function __construct(
        public int $user_id,
        public string $first_name,
        public ?string $last_name,
        public ?string $username,
        public bool $is_bot,
        public int $last_activity_time,
        public ?string $description,
        public ?string $avatar_url,
        public ?string $full_avatar_url,
        public ?array $commands,
    ) {
    }

    /**
     * @inheritdoc
     */
    public static function fromArray(array $data): static
    {
        return new static(
            (int)$data['user_id'],
            (string)$data['first_name'],
            $data['last_name'] ? (string)$data['last_name'] : null,
            $data['username'] ? (string)$data['username'] : null,
            (bool)$data['is_bot'],
            (int)$data['last_activity_time'],
            $data['description'] ? (string)$data['description'] : null,
            $data['avatar_url'] ? (string)$data['avatar_url'] : null,
            $data['full_avatar_url'] ? (string)$data['full_avatar_url'] : null,
            isset($data['commands']) && is_array($data['commands']) ? $data['commands'] : null,
        );
    }
}
