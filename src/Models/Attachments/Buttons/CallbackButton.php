<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons;

use BushlanovDev\MaxMessengerBot\Enums\ButtonType;
use BushlanovDev\MaxMessengerBot\Enums\Intent;

final readonly class CallbackButton extends AbstractButton
{
    public string $payload;
    public ?Intent $intent;

    public function __construct(string $text, string $payload, ?Intent $intent = null)
    {
        parent::__construct(ButtonType::Callback, $text);

        $this->payload = $payload;
        $this->intent = $intent;
    }
}
