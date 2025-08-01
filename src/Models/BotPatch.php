<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models;

use BushlanovDev\MaxMessengerBot\Models\Attachments\Payloads\PhotoAttachmentRequestPayload;

/**
 * Represents the data to patch for a bot. Instantiate with named arguments.
 *
 * Example: new BotPatch(name: 'New Bot Name', description: null);
 *
 * @property-read string|null $name
 * @property-read string|null $description
 * @property-read BotCommand[]|null $commands
 * @property-read PhotoAttachmentRequestPayload|null $photo
 */
final readonly class BotPatch extends AbstractPatchModel
{
}
