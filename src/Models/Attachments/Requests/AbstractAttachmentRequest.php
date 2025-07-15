<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models\Attachments\Requests;

use BushlanovDev\MaxMessengerBot\Enums\AttachmentType;
use BushlanovDev\MaxMessengerBot\Models\AbstractModel;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Payloads\AbstractAttachmentPayload;

/**
 * Message attachments.
 */
abstract readonly class AbstractAttachmentRequest extends AbstractModel
{
    public function __construct(
        public AttachmentType $type,
        public AbstractAttachmentPayload $payload,
    ) {
    }
}
