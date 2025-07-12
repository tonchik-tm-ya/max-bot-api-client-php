<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests\Models;

use BushlanovDev\MaxMessengerBot\Models\Message;
use BushlanovDev\MaxMessengerBot\Models\MessageBody;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Message::class)]
#[UsesClass(MessageBody::class)]
final class MessageTest extends TestCase
{
    #[Test]
    public function canBeCreatedFromArrayWithAllData(): void
    {
        $data = [
            'body' => [
                'mid' => 'mid.456.xyz',
                'seq' => 101,
                'text' => 'Hello, **world**!',
            ],
        ];

        $message = Message::fromArray($data);

        $this->assertInstanceOf(Message::class, $message);
        $this->assertInstanceOf(MessageBody::class, $message->body);

        $array = $message->toArray();

        $this->assertIsArray($array);
        $this->assertSame($data, $array);
    }
}
