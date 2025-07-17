<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests\Models\Attachments\Buttons;

use BushlanovDev\MaxMessengerBot\Enums\ButtonType;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons\RequestContactButton;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(RequestContactButton::class)]
final class RequestContactTest extends TestCase
{
    #[Test]
    public function toArraySerializesCorrectly(): void
    {
        $button = new RequestContactButton('Test Button');

        $expectedArray = [
            'type' => ButtonType::RequestContact->value,
            'text' => 'Test Button',
        ];

        $resultArray = $button->toArray();

        $this->assertSame($expectedArray, $resultArray);
    }
}
