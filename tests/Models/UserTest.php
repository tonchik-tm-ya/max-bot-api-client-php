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
            'description' => 'Description',
            'avatar_url' => 'https://example.com/avatar.jpg',
            'full_avatar_url' => 'https://example.com/full_avatar.jpg',
        ];

        $user = User::fromArray($data);

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame($data['user_id'], $user->userId);
        $this->assertSame($data['first_name'], $user->firstName);
        $this->assertSame($data['last_name'], $user->lastName);
        $this->assertSame($data['username'], $user->username);
        $this->assertSame($data['is_bot'], $user->isBot);
        $this->assertSame($data['last_activity_time'], $user->lastActivityTime);
        $this->assertSame($data['description'], $user->description);
        $this->assertSame($data['avatar_url'], $user->avatarUrl);
        $this->assertSame($data['full_avatar_url'], $user->fullAvatarUrl);

        $array = $user->toArray();

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

        $user = User::fromArray($data);

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame($data['user_id'], $user->userId);
        $this->assertSame($data['first_name'], $user->firstName);
        $this->assertNull($user->lastName);
        $this->assertNull($user->username);
        $this->assertSame($data['is_bot'], $user->isBot);
        $this->assertSame($data['last_activity_time'], $user->lastActivityTime);
        $this->assertNull($user->description);
        $this->assertNull($user->avatarUrl);
        $this->assertNull($user->fullAvatarUrl);

        $array = $user->toArray();

        $this->assertIsArray($array);
        $array = array_filter($array, fn($item) => null !== $item);
        $this->assertSame($data, $array);
    }
}
