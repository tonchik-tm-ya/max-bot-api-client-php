<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests\Models\Updates;

use BushlanovDev\MaxMessengerBot\Enums\UpdateType;
use BushlanovDev\MaxMessengerBot\Models\Updates\BotStartedUpdate;
use BushlanovDev\MaxMessengerBot\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BotStartedUpdate::class)]
#[UsesClass(User::class)]
final class BotStartedUpdateTest extends TestCase
{
    #[Test]
    public function canBeCreatedFromArray(): void
    {
        $data = [
            'update_type' => UpdateType::BotStarted->value,
            'timestamp' => 1678886400000,
            'chat_id' => 123,
            'user' => [
                'user_id' => 123,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'is_bot' => false,
                'last_activity_time' => 1678886400000,
                'avatar_url' => 'https://example.com/avatar.jpg',
            ],
            'user_locale' => 'ru-ru',
        ];

        $update = BotStartedUpdate::fromArray($data);

        $this->assertInstanceOf(BotStartedUpdate::class, $update);
        $this->assertSame(UpdateType::BotStarted, $update->updateType);
        $this->assertSame(123, $update->user->userId);
        $this->assertSame('John', $update->user->firstName);
        $this->assertSame('Doe', $update->user->lastName);
        $this->assertSame('https://example.com/avatar.jpg', $update->user->avatarUrl);
        $this->assertSame('ru-ru', $update->userLocale);
    }
}
