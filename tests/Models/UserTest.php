<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests\Models;

use BushlanovDev\MaxMessengerBot\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(User::class)]
final class UserTest extends TestCase
{
    #[Test]
    public function canBeCreatedFromArray(): void
    {
        $data = [
            'user_id' => 123,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'johndoe',
            'is_bot' => false,
            'last_activity_time' => 1678886400000,
        ];

        $sender = User::fromArray($data);

        $this->assertInstanceOf(User::class, $sender);
        $this->assertSame($data['user_id'], $sender->userId);
        $this->assertSame($data['first_name'], $sender->firstName);
        $this->assertSame($data['last_name'], $sender->lastName);
        $this->assertSame($data['username'], $sender->username);
        $this->assertSame($data['is_bot'], $sender->isBot);
        $this->assertSame($data['last_activity_time'], $sender->lastActivityTime);

        $array = $sender->toArray();

        $this->assertIsArray($array);
        $this->assertSame($data, $array);
    }

    #[Test]
    public function canBeCreatedFromArrayWithOptionalDataNull(): void
    {
        $data = [
            'user_id' => 123,
            'first_name' => 'John',
            'is_bot' => false,
            'last_activity_time' => 1678886400000,
        ];

        $sender = User::fromArray($data);

        $this->assertInstanceOf(User::class, $sender);
        $this->assertSame($data['user_id'], $sender->userId);
        $this->assertSame($data['first_name'], $sender->firstName);
        $this->assertNull($sender->lastName);
        $this->assertNull($sender->username);
        $this->assertSame($data['is_bot'], $sender->isBot);
        $this->assertSame($data['last_activity_time'], $sender->lastActivityTime);

        $array = $sender->toArray();

        $this->assertIsArray($array);
        unset($array['last_name']);
        unset($array['username']);
        $this->assertSame($data, $array);
    }
}
