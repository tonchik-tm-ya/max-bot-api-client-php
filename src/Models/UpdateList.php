<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models;

use BushlanovDev\MaxMessengerBot\Models\Updates\AbstractUpdate;

/**
 * List of all updates in chats your bot participated in.
 */
final readonly class UpdateList extends AbstractModel
{
    /**
     * @param AbstractUpdate[] $updates Page of updates.
     * @param int|null $marker Pointer to the next data page.
     */
    public function __construct(
        public array $updates,
        public ?int $marker,
    ) {
    }
}
