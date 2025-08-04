<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models\Attachments\Payloads;

use BushlanovDev\MaxMessengerBot\Models\AbstractModel;
use BushlanovDev\MaxMessengerBot\Models\User;

/**
 * Payload of a contact attachment.
 */
final readonly class ContactAttachmentPayload extends AbstractModel
{
    /**
     * @param string|null $vcfInfo User info in VCF format.
     * @param User|null $maxInfo User info if the contact is a Max user.
     */
    public function __construct(
        public ?string $vcfInfo,
        public ?User $maxInfo,
    ) {
    }
}
