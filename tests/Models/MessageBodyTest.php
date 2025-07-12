<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests\Models;

use BushlanovDev\MaxMessengerBot\Models\MessageBody;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(MessageBody::class)]
final class MessageBodyTest extends TestCase
{
    #[Test]
    public function canBeCreatedFromArrayWithAllData(): void
    {
        $data = [
            'mid' => 'mid.456.xyz',
            'seq' => 101,
            'text' => 'Hello, **world**!',
        ];

        $messageBody = MessageBody::fromArray($data);

        $this->assertInstanceOf(MessageBody::class, $messageBody);
        $this->assertSame($data['mid'], $messageBody->mid);
        $this->assertSame($data['seq'], $messageBody->seq);
        $this->assertSame($data['text'], $messageBody->text);

        $array = $messageBody->toArray();

        $this->assertIsArray($array);
        $this->assertSame($data, $array);
    }

    #[Test]
    public function canBeCreatedFromArrayWithOptionalDataNull(): void
    {
        $data = [
            'mid' => 'mid.456.xyz',
            'seq' => 101,
            'text' => null,
        ];

        $messageBody = MessageBody::fromArray($data);

        $this->assertInstanceOf(MessageBody::class, $messageBody);
        $this->assertSame($data['mid'], $messageBody->mid);
        $this->assertSame($data['seq'], $messageBody->seq);
        $this->assertNull($messageBody->text);

        $array = $messageBody->toArray();

        $this->assertIsArray($array);
        $this->assertSame($data, $array);
    }
}
