<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons;

use BushlanovDev\MaxMessengerBot\Enums\ButtonType;
use BushlanovDev\MaxMessengerBot\Enums\Intent;

/**
 * Sends a notification with payload to a bot (via WebHook or long polling).
 */
final readonly class CallbackButton extends AbstractButton
{
    public string $payload;
    public ?Intent $intent;

    /**
     * @param string $text Visible button text (1 to 128 characters).
     * @param string $payload Button token (up to 1024 characters).
     * @param Intent|null $intent The intent of the button. Affects how it is displayed by the client.
     */
    public function __construct(string $text, string $payload, ?Intent $intent = null)
    {
        parent::__construct(ButtonType::Callback, $text);

        $this->payload = $payload;
        $this->intent = $intent;
    }
}
