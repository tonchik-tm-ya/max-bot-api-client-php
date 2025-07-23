<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests\Models\Attachments\Requests;

use BushlanovDev\MaxMessengerBot\Enums\AttachmentType;
use BushlanovDev\MaxMessengerBot\Enums\ButtonType;
use BushlanovDev\MaxMessengerBot\Enums\Intent;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons\AbstractButton;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons\CallbackButton;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons\LinkButton;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Payloads\InlineKeyboardPayload;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Requests\InlineKeyboardAttachmentRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InlineKeyboardAttachmentRequest::class)]
#[UsesClass(InlineKeyboardPayload::class)]
#[UsesClass(AbstractButton::class)]
#[UsesClass(CallbackButton::class)]
#[UsesClass(LinkButton::class)]
final class InlineKeyboardAttachmentRequestTest extends TestCase
{
    #[Test]
    public function createsCorrectRequestAndSerializesToArray(): void
    {
        $buttons = [
            [new CallbackButton('Press Me', 'cb_payload_1')],
            [new LinkButton('Docs', 'https://dev.max.ru')],
        ];
        $request = new InlineKeyboardAttachmentRequest($buttons);

        $this->assertInstanceOf(InlineKeyboardAttachmentRequest::class, $request);
        $this->assertSame(AttachmentType::InlineKeyboard, $request->type);
        $this->assertInstanceOf(InlineKeyboardPayload::class, $request->payload);
        $this->assertSame($buttons, $request->payload->buttons);

        $expectedArray = [
            'type' => 'inline_keyboard',
            'payload' => [
                'buttons' => [
                    [
                        [
                            'type' => ButtonType::Callback->value,
                            'text' => 'Press Me',
                            'payload' => 'cb_payload_1',
                            'intent' => null,
                        ],
                    ],
                    [
                        [
                            'type' => ButtonType::Link->value,
                            'text' => 'Docs',
                            'url' => 'https://dev.max.ru',
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expectedArray, $request->toArray());
    }

    #[Test]
    public function handlesJaggedAndMultiButtonRows(): void
    {
        $buttons = [
            [new CallbackButton('Positive', 'ok', Intent::Positive)],
            [
                new CallbackButton('Negative', 'no', Intent::Negative),
                new LinkButton('Help', 'https://example.com/help'),
            ],
        ];
        $request = new InlineKeyboardAttachmentRequest($buttons);

        $expectedArray = [
            'type' => 'inline_keyboard',
            'payload' => [
                'buttons' => [
                    [
                        [
                            'type' => ButtonType::Callback->value,
                            'text' => 'Positive',
                            'payload' => 'ok',
                            'intent' => Intent::Positive->value,
                        ],
                    ],
                    [
                        [
                            'type' => ButtonType::Callback->value,
                            'text' => 'Negative',
                            'payload' => 'no',
                            'intent' => Intent::Negative->value,
                        ],
                        [
                            'type' => ButtonType::Link->value,
                            'text' => 'Help',
                            'url' => 'https://example.com/help',
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expectedArray, $request->toArray());
    }

    #[Test]
    public function canBeCreatedWithEmptyButtonsArray(): void
    {
        $buttons = [];
        $request = new InlineKeyboardAttachmentRequest($buttons);

        $this->assertEmpty($request->payload->buttons);

        $expectedArray = [
            'type' => 'inline_keyboard',
            'payload' => [
                'buttons' => $buttons,
            ],
        ];
        $this->assertEquals($expectedArray, $request->toArray());
    }
}
