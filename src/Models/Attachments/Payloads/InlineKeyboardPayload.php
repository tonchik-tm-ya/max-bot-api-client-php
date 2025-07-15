<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models\Attachments\Payloads;

use BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons\AbstractButton;

final readonly class InlineKeyboardPayload extends AbstractAttachmentPayload
{
    /**
     * @param AbstractButton[][] $buttons
     */
    public function __construct(
        public array $buttons,
    ) {
    }
}
