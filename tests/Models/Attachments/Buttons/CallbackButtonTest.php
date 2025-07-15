<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests\Models\Attachments\Buttons;

use BushlanovDev\MaxMessengerBot\Enums\ButtonType;
use BushlanovDev\MaxMessengerBot\Enums\Intent;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons\CallbackButton;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CallbackButton::class)]
final class CallbackButtonTest extends TestCase
{
    #[Test]
    public function toArraySerializesCorrectly(): void
    {
        $button = new CallbackButton('Test Button', 'test_payload', Intent::Default);

        $expectedArray = [
            'payload' => 'test_payload',
            'intent' => Intent::Default->value,
            'type' => ButtonType::Callback->value,
            'text' => 'Test Button',
        ];

        $resultArray = $button->toArray();

        $this->assertSame($expectedArray, $resultArray);
    }
}
