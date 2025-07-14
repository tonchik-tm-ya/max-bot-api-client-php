<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests;

use BushlanovDev\MaxMessengerBot\Attributes\ArrayOf;
use BushlanovDev\MaxMessengerBot\Enums\UpdateType;
use BushlanovDev\MaxMessengerBot\ModelFactory;
use BushlanovDev\MaxMessengerBot\Models\BotCommand;
use BushlanovDev\MaxMessengerBot\Models\BotInfo;
use BushlanovDev\MaxMessengerBot\Models\Message;
use BushlanovDev\MaxMessengerBot\Models\MessageBody;
use BushlanovDev\MaxMessengerBot\Models\Recipient;
use BushlanovDev\MaxMessengerBot\Models\Result;
use BushlanovDev\MaxMessengerBot\Models\Sender;
use BushlanovDev\MaxMessengerBot\Models\Subscription;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ModelFactory::class)]
#[UsesClass(BotInfo::class)]
#[UsesClass(BotCommand::class)]
#[UsesClass(Result::class)]
#[UsesClass(Subscription::class)]
#[UsesClass(ArrayOf::class)]
#[UsesClass(Message::class)]
#[UsesClass(MessageBody::class)]
#[UsesClass(Recipient::class)]
#[UsesClass(Sender::class)]
final class ModelFactoryTest extends TestCase
{
    private ModelFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new ModelFactory();
    }

    #[Test]
    public function createResultSuccessfully(): void
    {
        $rawData = ['success' => true];

        $result = $this->factory->createResult($rawData);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->success);
        $this->assertNull($result->message);
    }

    #[Test]
    public function createResultNotSuccessfully()
    {
        $rawData = [
            'success' => false,
            'message' => 'error message',
        ];

        $result = $this->factory->createResult($rawData);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertFalse($result->success);
        $this->assertSame('error message', $result->message);
    }

    #[Test]
    public function createBotInfoCorrectlyHydratesCommands(): void
    {
        $rawData = [
            'user_id' => 12345,
            'first_name' => 'Test',
            'last_name' => 'Bot',
            'username' => 'test_bot',
            'is_bot' => true,
            'last_activity_time' => 1678886400000,
            'description' => 'A test bot.',
            'avatar_url' => 'http://example.com/avatar.jpg',
            'full_avatar_url' => 'http://example.com/full_avatar.jpg',
            'commands' => [
                ['name' => 'start', 'description' => 'Start the bot'],
                ['name' => 'help', 'description' => 'Show help'],
            ],
        ];

        $botInfo = $this->factory->createBotInfo($rawData);

        $this->assertInstanceOf(BotInfo::class, $botInfo);
        $this->assertSame(12345, $botInfo->userId);

        $this->assertIsArray($botInfo->commands);
        $this->assertCount(2, $botInfo->commands);
        $this->assertInstanceOf(BotCommand::class, $botInfo->commands[0]);
        $this->assertSame('start', $botInfo->commands[0]->name);
        $this->assertInstanceOf(BotCommand::class, $botInfo->commands[1]);
        $this->assertSame('help', $botInfo->commands[1]->name);
    }

    #[Test]
    public function createBotInfoHandlesNullCommands(): void
    {
        $rawData = [
            'user_id' => 12345,
            'first_name' => 'Test',
            'last_name' => null,
            'username' => 'test_bot',
            'is_bot' => true,
            'last_activity_time' => 1678886400000,
            'description' => null,
            'avatar_url' => null,
            'full_avatar_url' => null,
            'commands' => null,
        ];

        $botInfo = $this->factory->createBotInfo($rawData);

        $this->assertInstanceOf(BotInfo::class, $botInfo);
        $this->assertNull($botInfo->commands);
    }

    #[Test]
    public function createSubscriptions(): void
    {
        $rawData = [
            'subscriptions' => [
                [
                    'url' => 'https://example.com/webhook',
                    'time' => 1678886400000,
                    'update_types' => ['message_created'],
                    'version' => '0.0.1',
                ],
            ],
        ];

        $subscriptions = $this->factory->createSubscriptions($rawData);

        $this->assertIsArray($subscriptions);
        $this->assertCount(1, $subscriptions);
        $this->assertInstanceOf(Subscription::class, $subscriptions[0]);
        $this->assertSame(UpdateType::MessageCreated, $subscriptions[0]->updateTypes[0]);
    }

    #[Test]
    public function createMessage(): void
    {
        $rawData = [
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
            'sender' =>[
                'user_id' => 123,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'username' => 'johndoe',
                'is_bot' => false,
                'last_activity_time' => 1678886400000,
            ],
            'url' => 'https://max.ru/message/123',
        ];

        $message = $this->factory->createMessage($rawData);

        $this->assertInstanceOf(Message::class, $message);
        $this->assertInstanceOf(MessageBody::class, $message->body);
        $this->assertInstanceOf(Recipient::class, $message->recipient);
        $this->assertInstanceOf(Sender::class, $message->sender);
    }
}
