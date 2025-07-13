<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests\Models;

use BushlanovDev\MaxMessengerBot\Models\Message;
use BushlanovDev\MaxMessengerBot\Models\MessageBody;
use BushlanovDev\MaxMessengerBot\Models\Recipient;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Message::class)]
#[UsesClass(MessageBody::class)]
#[UsesClass(Recipient::class)]
final class MessageTest extends TestCase
{
    #[Test]
    public function canBeCreatedFromArrayWithAllData(): void
    {
        $data = [
            'timestamp' => time(),
            'body' => [
                'mid' => 'mid.456.xyz',
                'seq' => 101,
                'text' => 'Hello, **world**!',
            ],
            'recipient' => [
                'chat_type' => 'dialog',
                'user_id' => 123,
                'chat_id' => null,
            ],
        ];

        $message = Message::fromArray($data);

        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame($data['timestamp'], $message->timestamp);
        $this->assertInstanceOf(MessageBody::class, $message->body);
        $this->assertInstanceOf(Recipient::class, $message->recipient);

        $array = $message->toArray();

        $this->assertIsArray($array);
        $this->assertSame($data, $array);
    }
}
