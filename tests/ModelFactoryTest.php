<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests;

use BushlanovDev\MaxMessengerBot\Attributes\ArrayOf;
use BushlanovDev\MaxMessengerBot\Enums\ChatAdminPermission;
use BushlanovDev\MaxMessengerBot\Enums\UpdateType;
use BushlanovDev\MaxMessengerBot\ModelFactory;
use BushlanovDev\MaxMessengerBot\Models\BotCommand;
use BushlanovDev\MaxMessengerBot\Models\BotInfo;
use BushlanovDev\MaxMessengerBot\Models\Chat;
use BushlanovDev\MaxMessengerBot\Models\ChatList;
use BushlanovDev\MaxMessengerBot\Models\ChatMember;
use BushlanovDev\MaxMessengerBot\Models\Image;
use BushlanovDev\MaxMessengerBot\Models\Message;
use BushlanovDev\MaxMessengerBot\Models\MessageBody;
use BushlanovDev\MaxMessengerBot\Models\Recipient;
use BushlanovDev\MaxMessengerBot\Models\Result;
use BushlanovDev\MaxMessengerBot\Models\Sender;
use BushlanovDev\MaxMessengerBot\Models\Subscription;
use BushlanovDev\MaxMessengerBot\Models\UpdateList;
use BushlanovDev\MaxMessengerBot\Models\Updates\BotStartedUpdate;
use BushlanovDev\MaxMessengerBot\Models\Updates\ChatTitleChangedUpdate;
use BushlanovDev\MaxMessengerBot\Models\Updates\MessageChatCreatedUpdate;
use BushlanovDev\MaxMessengerBot\Models\Updates\MessageCreatedUpdate;
use BushlanovDev\MaxMessengerBot\Models\UploadEndpoint;
use BushlanovDev\MaxMessengerBot\Models\User;
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
#[UsesClass(UpdateList::class)]
#[UsesClass(BotStartedUpdate::class)]
#[UsesClass(MessageCreatedUpdate::class)]
#[UsesClass(User::class)]
#[UsesClass(UploadEndpoint::class)]
#[UsesClass(Chat::class)]
#[UsesClass(Image::class)]
#[UsesClass(ChatTitleChangedUpdate::class)]
#[UsesClass(MessageChatCreatedUpdate::class)]
#[UsesClass(ChatList::class)]
#[UsesClass(ChatMember::class)]
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
            'sender' => [
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

    #[Test]
    public function createUploadEndpoint(): void
    {
        $rawData = [
            'url' => 'https://example.com/upload',
        ];

        $uploadEndpoint = $this->factory->createUploadEndpoint($rawData);

        $this->assertInstanceOf(UploadEndpoint::class, $uploadEndpoint);
        $this->assertSame('https://example.com/upload', $uploadEndpoint->url);
        $this->assertNull($uploadEndpoint->token);
    }

    #[Test]
    public function createChat(): void
    {
        $rawData = [
            'chat_id' => 123,
            'type' => 'chat',
            'status' => 'active',
            'last_event_time' => 1678886400000,
            'participants_count' => 50,
            'is_public' => false,
            'title' => 'Test Chat',
            'icon' => [
                'url' => 'https://example.com/icon.jpg',
            ],
            'owner_id' => 123,
            'link' => 'https://max.ru/chat/123',
            'description' => 'This is a test chat',
            'dialog_with_user' => [
                'user_id' => 456,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'username' => 'johndoe',
                'is_bot' => false,
                'last_activity_time' => 1678886400000,
            ],
            'messages_count' => 100,
            'chat_message_id' => 'mid.123',
        ];

        $chat = $this->factory->createChat($rawData);

        $this->assertInstanceOf(Chat::class, $chat);
        $this->assertInstanceOf(Image::class, $chat->icon);
        $this->assertInstanceOf(User::class, $chat->dialogWithUser);
    }

    #[Test]
    public function createUpdateListHandlesDifferentUpdateTypes(): void
    {
        $rawData = [
            'updates' => [
                [
                    'update_type' => 'message_created',
                    'timestamp' => 1,
                    'message' => [
                        'timestamp' => 1,
                        'body' => ['mid' => 'mid.1', 'seq' => 1],
                        'recipient' => ['chat_type' => 'dialog'],
                    ],
                ],
                [
                    'update_type' => 'bot_started',
                    'timestamp' => 2,
                    'chat_id' => 123,
                    'user' => [
                        'user_id' => 123,
                        'first_name' => 'John',
                        'is_bot' => false,
                        'last_activity_time' => 2,
                    ],
                    'payload' => 'start_payload',
                    'user_locale' => 'ru-RU',
                ],
                [
                    'update_type' => 'chat_title_changed',
                    'timestamp' => 1680000000,
                    'chat_id' => 12345,
                    'user' => [
                        'user_id' => 54321,
                        'first_name' => 'John',
                        'is_bot' => false,
                        'last_activity_time' => 1679999999,
                    ],
                    'title' => 'New Awesome Chat Title',
                ],
                [
                    'update_type' => 'message_chat_created',
                    'timestamp' => 1683000001,
                    'chat' => [
                        'chat_id' => 54321,
                        'type' => 'chat',
                        'status' => 'active',
                        'last_event_time' => 1683000001,
                        'participants_count' => 1,
                        'is_public' => false,
                        'title' => 'Another Discussion',
                    ],
                    'message_id' => 'mid.another.message',
                    'start_payload' => null,
                ],
            ],
            'marker' => 12345,
        ];

        $updateList = $this->factory->createUpdateList($rawData);

        $this->assertInstanceOf(UpdateList::class, $updateList);
        $this->assertSame(12345, $updateList->marker);
        $this->assertCount(4, $updateList->updates);
        $this->assertInstanceOf(MessageCreatedUpdate::class, $updateList->updates[0]);
        $this->assertInstanceOf(BotStartedUpdate::class, $updateList->updates[1]);
        $this->assertSame('start_payload', $updateList->updates[1]->payload);
        $this->assertInstanceOf(ChatTitleChangedUpdate::class, $updateList->updates[2]);
        $this->assertInstanceOf(MessageChatCreatedUpdate::class, $updateList->updates[3]);
    }

    #[Test]
    public function createChatListDelegatesCreationToChatListModel(): void
    {
        $rawData = [
            'chats' => [
                [
                    'chat_id' => 101,
                    'type' => 'chat',
                    'status' => 'active',
                    'last_event_time' => 1,
                    'participants_count' => 5,
                    'is_public' => false,
                ],
                [
                    'chat_id' => 102,
                    'type' => 'dialog',
                    'status' => 'suspended',
                    'last_event_time' => 2,
                    'participants_count' => 2,
                    'is_public' => false,
                ],
            ],
            'marker' => 98765,
        ];

        $chatList = $this->factory->createChatList($rawData);

        $this->assertInstanceOf(ChatList::class, $chatList);

        $this->assertSame(98765, $chatList->marker);
        $this->assertCount(2, $chatList->chats);
        $this->assertInstanceOf(Chat::class, $chatList->chats[0]);
        $this->assertSame(101, $chatList->chats[0]->chatId);
    }

    #[Test]
    public function createChatMember()
    {
        $rawData = [
            'user_id' => 101,
            'first_name' => 'AdminBot',
            'last_name' => null,
            'username' => 'admin_bot',
            'is_bot' => true,
            'last_activity_time' => 1678886400,
            'description' => 'I am a bot.',
            'avatar_url' => null,
            'full_avatar_url' => null,
            'last_access_time' => 1679000000,
            'is_owner' => false,
            'is_admin' => true,
            'join_time' => 1678000000,
            'permissions' => ['pin_message', 'write'],
        ];

        $chatMember = $this->factory->createChatMember($rawData);

        $this->assertInstanceOf(ChatMember::class, $chatMember);
        $this->assertTrue($chatMember->isAdmin);
        $this->assertFalse($chatMember->isOwner);
        $this->assertIsArray($chatMember->permissions);
        $this->assertCount(2, $chatMember->permissions);
        $this->assertSame(ChatAdminPermission::PinMessage, $chatMember->permissions[0]);
        $this->assertSame(ChatAdminPermission::Write, $chatMember->permissions[1]);
        $this->assertEquals($rawData, $chatMember->toArray());
    }

    #[Test]
    public function createMessagesReturnsArrayOfMessageObjects(): void
    {
        $data = [
            'messages' => [
                [
                    'timestamp' => 1,
                    'body' => ['mid' => 'mid.1', 'seq' => 1],
                    'recipient' => ['chat_type' => 'chat', 'chat_id' => 123],
                ],
                [
                    'timestamp' => 2,
                    'body' => ['mid' => 'mid.2', 'seq' => 2],
                    'recipient' => ['chat_type' => 'chat', 'chat_id' => 123],
                ],
            ],
        ];

        $messages = $this->factory->createMessages($data);

        $this->assertIsArray($messages);
        $this->assertCount(2, $messages);
        $this->assertInstanceOf(Message::class, $messages[0]);
        $this->assertSame('mid.1', $messages[0]->body->mid);
    }

    #[Test]
    public function createMessagesHandlesEmptyOrMissingKey(): void
    {
        $this->assertEmpty($this->factory->createMessages(['messages' => []]));
        $this->assertEmpty($this->factory->createMessages([]));
    }
}
