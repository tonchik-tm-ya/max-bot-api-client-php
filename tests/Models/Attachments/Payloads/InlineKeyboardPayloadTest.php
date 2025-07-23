<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests\Models\Attachments\Payloads;

use BushlanovDev\MaxMessengerBot\Enums\ButtonType;
use BushlanovDev\MaxMessengerBot\Enums\Intent;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons\CallbackButton;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons\LinkButton;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Payloads\InlineKeyboardPayload;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InlineKeyboardPayload::class)]
#[UsesClass(CallbackButton::class)]
#[UsesClass(LinkButton::class)]
final class InlineKeyboardPayloadTest extends TestCase
{
    #[Test]
    public function constructionAndPropertyAccess(): void
    {
        $buttons = [
            [new CallbackButton('Test', 'payload')],
        ];
        $payload = new InlineKeyboardPayload($buttons);

        $this->assertInstanceOf(InlineKeyboardPayload::class, $payload);
        $this->assertSame($buttons, $payload->buttons);
    }

    #[Test]
    public function toArraySerializesCorrectly(): void
    {
        $buttons = [
            [new CallbackButton('Accept', 'accept_payload', Intent::Positive)],
            [
                new CallbackButton('Decline', 'decline_payload', Intent::Negative),
                new LinkButton('Help', 'https://example.com/help'),
            ],
        ];
        $payload = new InlineKeyboardPayload($buttons);

        $resultArray = $payload->toArray();

        $expectedArray = [
            'buttons' => [
                [
                    [
                        'type' => ButtonType::Callback->value,
                        'text' => 'Accept',
                        'payload' => 'accept_payload',
                        'intent' => Intent::Positive->value,
                    ],
                ],
                [
                    [
                        'type' => ButtonType::Callback->value,
                        'text' => 'Decline',
                        'payload' => 'decline_payload',
                        'intent' => Intent::Negative->value,
                    ],
                    [
                        'type' => ButtonType::Link->value,
                        'text' => 'Help',
                        'url' => 'https://example.com/help',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expectedArray, $resultArray);
    }

    #[Test]
    public function toArrayHandlesEmptyButtonsArray(): void
    {
        $payload = new InlineKeyboardPayload([]);
        $resultArray = $payload->toArray();

        $expectedArray = [
            'buttons' => [],
        ];
        $this->assertEquals($expectedArray, $resultArray);
    }
}
