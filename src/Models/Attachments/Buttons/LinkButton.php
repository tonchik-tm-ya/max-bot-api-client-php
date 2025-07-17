<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons;

use BushlanovDev\MaxMessengerBot\Enums\ButtonType;

/**
 * Makes a user to follow a link.
 */
final readonly class LinkButton extends AbstractButton
{
    public string $url;

    /**
     * @param string $text Visible button text (1 to 128 characters).
     * @param string $url Button URL (1 to 2048 characters).
     */
    public function __construct(string $text, string $url)
    {
        parent::__construct(ButtonType::Link, $text);

        $this->url = $url;
    }
}
