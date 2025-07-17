<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests\Models\Attachments\Buttons;

use BushlanovDev\MaxMessengerBot\Enums\ButtonType;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons\RequestGeoLocationButton;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(RequestGeoLocationButton::class)]
final class RequestGeoLocationTest extends TestCase
{
    #[Test]
    public function toArraySerializesCorrectly(): void
    {
        $button = new RequestGeoLocationButton('Test Button');

        $expectedArray = [
            'quick' => false,
            'type' => ButtonType::RequestGeoLocation->value,
            'text' => 'Test Button',
        ];

        $resultArray = $button->toArray();

        $this->assertSame($expectedArray, $resultArray);
    }

    #[Test]
    public function toArraySerializesCorrectlyWithQuick(): void
    {
        $button = new RequestGeoLocationButton('Test Button', true);

        $expectedArray = [
            'quick' => true,
            'type' => ButtonType::RequestGeoLocation->value,
            'text' => 'Test Button',
        ];

        $resultArray = $button->toArray();

        $this->assertSame($expectedArray, $resultArray);
    }
}
