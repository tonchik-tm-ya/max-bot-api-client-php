<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models\Attachments\Requests;

use BushlanovDev\MaxMessengerBot\Enums\AttachmentType;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons\AbstractButton;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Payloads\InlineKeyboardPayload;

final readonly class InlineKeyboardAttachmentRequest extends AbstractAttachmentRequest
{
    /**
     * @param AbstractButton[][] $buttons
     */
    public function __construct(array $buttons)
    {
        parent::__construct(
            AttachmentType::InlineKeyboard,
            new InlineKeyboardPayload($buttons)
        );
    }
}
