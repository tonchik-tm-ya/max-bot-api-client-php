<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons;

use BushlanovDev\MaxMessengerBot\Enums\ButtonType;
use BushlanovDev\MaxMessengerBot\Models\AbstractModel;

abstract readonly class AbstractButton extends AbstractModel
{
    public function __construct(
        public ButtonType $type,
        public string $text,
    ) {
    }
}
