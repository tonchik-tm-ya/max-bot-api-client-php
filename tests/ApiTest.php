<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests;

use BushlanovDev\MaxMessengerBot\Api;
use BushlanovDev\MaxMessengerBot\Client;
use BushlanovDev\MaxMessengerBot\ClientApiInterface;
use BushlanovDev\MaxMessengerBot\Enums\MessageFormat;
use BushlanovDev\MaxMessengerBot\Enums\UpdateType;
use BushlanovDev\MaxMessengerBot\ModelFactory;
use BushlanovDev\MaxMessengerBot\Models\BotInfo;
use BushlanovDev\MaxMessengerBot\Models\Message;
use BushlanovDev\MaxMessengerBot\Models\MessageBody;
use BushlanovDev\MaxMessengerBot\Models\Recipient;
use BushlanovDev\MaxMessengerBot\Models\Result;
use BushlanovDev\MaxMessengerBot\Models\Subscription;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(Api::class)]
#[UsesClass(Result::class)]
#[UsesClass(Client::class)]
#[UsesClass(BotInfo::class)]
#[UsesClass(Subscription::class)]
#[UsesClass(Message::class)]
#[UsesClass(MessageBody::class)]
#[UsesClass(Recipient::class)]
final class ApiTest extends TestCase
{
    private MockObject&ClientApiInterface $clientMock;
    private MockObject&ModelFactory $modelFactoryMock;
    private Api $api;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->clientMock = $this->createMock(ClientApiInterface::class);
        $this->modelFactoryMock = $this->createMock(ModelFactory::class);

        $this->api = new Api('fake-token', $this->clientMock, $this->modelFactoryMock);
    }

    #[Test]
    public function constructorCanCreateDefaultDependencies(): void
    {
        $api = new Api('some-token');

        $reflection = new ReflectionClass($api);

        $clientProp = $reflection->getProperty('client');
        $this->assertInstanceOf(ClientApiInterface::class, $clientProp->getValue($api));

        $factoryProp = $reflection->getProperty('modelFactory');
        $this->assertInstanceOf(ModelFactory::class, $factoryProp->getValue($api));
    }

    #[Test]
    public function getBotInfoCallsClientAndFactoryCorrectly(): void
    {
        $rawResponseData = ['user_id' => 123, 'first_name' => 'ApiTestBot'];

        $botInfo = new BotInfo(
            123,
            'ApiTestBot',
            null, null, true, 0, null, null, null, null,
        );

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('GET', '/me')
            ->willReturn($rawResponseData);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createBotInfo')
            ->with($rawResponseData)
            ->willReturn($botInfo);

        $result = $this->api->getBotInfo();

        $this->assertSame($botInfo, $result);
    }

    #[Test]
    public function testSubscribeCallsClientAndFactoryCorrectly(): void
    {
        $rawResponseData = [
            'subscriptions' => [
                [
                    'url' => 'https://example.com/webhook',
                    'time' => 1678886400000,
                    'update_types' => ['message_created'],
                    'version' => '0.0.1',
                ],
            ],
        ];

        $subscription = new Subscription(
            'https://example.com/webhook',
            1678886400000,
            [UpdateType::MessageCreated],
            '0.0.1',
        );

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('GET', '/subscriptions')
            ->willReturn($rawResponseData);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createSubscriptions')
            ->with($rawResponseData)
            ->willReturn([$subscription]);

        $result = $this->api->getSubscriptions();

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(Subscription::class, $result[0]);
        $this->assertSame(UpdateType::MessageCreated, $result[0]->updateTypes[0]);
    }

    #[Test]
    public function subscribeCallsClientWithAllParameters(): void
    {
        $url = 'https://example.com/webhook';
        $secret = 'secure';
        $updateTypes = [UpdateType::MessageCreated, UpdateType::BotStarted];
        $updateTypesAsStrings = array_map(fn($type) => $type->value, $updateTypes);

        $expectedBody = [
            'url' => $url,
            'secret' => $secret,
            'update_types' => $updateTypesAsStrings,
        ];

        $rawClientResponse = ['success' => true];
        $expectedResultObject = new Result(true, null);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('POST', '/subscriptions', [], $expectedBody)
            ->willReturn($rawClientResponse);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createResult')
            ->with($rawClientResponse)
            ->willReturn($expectedResultObject);

        $result = $this->api->subscribe($url, $secret, $updateTypes);
        $this->assertSame($expectedResultObject, $result);
    }

    #[Test]
    public function subscribeHandlesOptionalParametersAsNull(): void
    {
        $url = 'https://example.com/webhook';

        $expectedBody = [
            'url' => $url,
            'secret' => null,
            'update_types' => null,
        ];

        $rawClientResponse = ['success' => true];
        $expectedResultObject = new Result(true, null);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('POST', '/subscriptions', [], $expectedBody)
            ->willReturn($rawClientResponse);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createResult')
            ->with($rawClientResponse)
            ->willReturn($expectedResultObject);

        $result = $this->api->subscribe($url);
        $this->assertSame($expectedResultObject, $result);
    }

    #[Test]
    public function unsubscribeCallsClientWithCorrectParameters(): void
    {
        $url = 'https://example.com/webhook';
        $expectedQueryParams = ['url' => $url];

        $rawClientResponse = ['success' => true];
        $expectedResultObject = new Result(true, null);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('DELETE', '/subscriptions', $expectedQueryParams, [])
            ->willReturn($rawClientResponse);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createResult')
            ->with($rawClientResponse)
            ->willReturn($expectedResultObject);

        $result = $this->api->unsubscribe($url);
        $this->assertSame($expectedResultObject, $result);
    }

    #[Test]
    public function sendMessageBuildsCorrectRequestForAllParameters(): void
    {
        $chatId = 123456;
        $text = 'Hello, **world**!';
        $format = MessageFormat::Markdown;
        $notify = false;
        $disableLinkPreview = true;

        $expectedQuery = [
            'chat_id' => $chatId,
            'disable_link_preview' => $disableLinkPreview,
        ];

        $expectedBody = [
            'text' => $text,
            'format' => $format->value,
            'notify' => $notify,
        ];

        $apiResponse = [
            'message' => [
                'timestamp' => time(),
                'body' => ['mid' => 'mid.456.xyz', 'seq' => 101, 'text' => $text],
                'recipient' => ['chat_type' => 'dialog', 'user_id' => 123, 'chat_id' => null],
            ],
        ];

        $expectedMessageObject = Message::fromArray($apiResponse['message']);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('POST', '/messages', $expectedQuery, $expectedBody)
            ->willReturn($apiResponse);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createMessage')
            ->with($apiResponse['message'])
            ->willReturn($expectedMessageObject);

        $result = $this->api->sendMessage(
            null,
            $chatId,
            $text,
            $format,
            $notify,
            $disableLinkPreview,
        );

        $this->assertSame($expectedMessageObject, $result);
    }
}
