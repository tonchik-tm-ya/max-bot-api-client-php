<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons;

use BushlanovDev\MaxMessengerBot\Enums\ButtonType;

/**
 * After pressing this type of button client sends new message with attachment of current user geo location.
 */
final readonly class RequestGeoLocationButton extends AbstractButton
{
    public bool $quick;

    /**
     * @param string $text Visible button text (1 to 128 characters).
     * @param bool $quick If true, sends location without asking user's confirmation.
     */
    public function __construct(string $text, bool $quick = false)
    {
        parent::__construct(ButtonType::RequestGeoLocation, $text);

        $this->quick = $quick;
    }
}
