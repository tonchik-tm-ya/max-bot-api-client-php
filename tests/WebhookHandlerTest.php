<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests;

use BushlanovDev\MaxMessengerBot\Api;
use BushlanovDev\MaxMessengerBot\Enums\ChatType;
use BushlanovDev\MaxMessengerBot\Exceptions\SecurityException;
use BushlanovDev\MaxMessengerBot\Exceptions\SerializationException;
use BushlanovDev\MaxMessengerBot\ModelFactory;
use BushlanovDev\MaxMessengerBot\Models\Message;
use BushlanovDev\MaxMessengerBot\Models\MessageBody;
use BushlanovDev\MaxMessengerBot\Models\Recipient;
use BushlanovDev\MaxMessengerBot\Models\Updates\AbstractUpdate;
use BushlanovDev\MaxMessengerBot\Models\Updates\MessageCreatedUpdate;
use BushlanovDev\MaxMessengerBot\WebhookHandler;
use GuzzleHttp\Psr7\ServerRequest;
use LogicException;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[CoversClass(WebhookHandler::class)]
#[UsesClass(Message::class)]
#[UsesClass(MessageBody::class)]
#[UsesClass(Recipient::class)]
#[UsesClass(AbstractUpdate::class)]
#[UsesClass(MessageCreatedUpdate::class)]
final class WebhookHandlerTest extends TestCase
{
    use PHPMock;

    private MockObject&Api $apiMock;
    private MockObject&ModelFactory $modelFactoryMock;
    private MockObject&LoggerInterface $loggerMock;

    private const string SECRET = 'my-super-secret-key';

    protected function setUp(): void
    {
        parent::setUp();
        $this->apiMock = $this->createMock(Api::class);
        $this->modelFactoryMock = $this->createMock(ModelFactory::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
    }

    private function createValidUpdatePayload(): string
    {
        return json_encode([
            'update_type' => 'message_created',
            'timestamp' => 1678886400,
            'message' => [
                'timestamp' => 1678886400,
                'body' => ['mid' => 'm.123', 'seq' => 1, 'text' => 'Hello World'],
                'recipient' => ['chat_type' => 'dialog', 'chat_id' => 101, 'user_id' => 101],
                'sender' => null,
                'url' => null,
            ],
            'user_locale' => 'ru-RU',
        ]);
    }

    private function createRealUpdateObject(array $data): MessageCreatedUpdate
    {
        $messageBody = new MessageBody(
            $data['message']['body']['mid'],
            $data['message']['body']['seq'],
            $data['message']['body']['text'],
            null,
            null,
        );
        $recipient = new Recipient(
            ChatType::from($data['message']['recipient']['chat_type']),
            $data['message']['recipient']['user_id'],
            $data['message']['recipient']['chat_id']
        );
        $message = new Message(
            $data['message']['timestamp'],
            $recipient,
            $messageBody,
            null,
            null,
            null,
            null,
        );

        return new MessageCreatedUpdate(
            $data['timestamp'],
            $message,
            $data['user_locale']
        );
    }

    #[Test]
    public function handleMethodProcessesPsr7RequestAndDispatches(): void
    {
        $payload = $this->createValidUpdatePayload();
        $signature = self::SECRET;
        $updateData = json_decode($payload, true);
        $expectedUpdate = $this->createRealUpdateObject($updateData);

        $request = new ServerRequest(
            'POST', '/webhook', ['X-Max-Bot-Api-Secret' => $signature], $payload
        );

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createUpdate')
            ->with($updateData)
            ->willReturn($expectedUpdate);

        $handlerWasCalled = false;
        $webhookHandler = new WebhookHandler($this->apiMock, $this->modelFactoryMock, self::SECRET);
        $webhookHandler->onMessageCreated(function (AbstractUpdate $update) use (&$handlerWasCalled, $expectedUpdate) {
            $this->assertSame($expectedUpdate, $update);
            $handlerWasCalled = true;
        });

        $webhookHandler->handle($request);

        $this->assertTrue($handlerWasCalled, 'The registered handler was not dispatched from handle() method.');
    }

    #[Test]
    public function dispatchCallsCorrectHandlerForRegisteredEvent(): void
    {
        $handlerWasCalled = false;
        $updateData = json_decode($this->createValidUpdatePayload(), true);
        $testUpdate = $this->createRealUpdateObject($updateData);
        $webhookHandler = new WebhookHandler($this->apiMock, $this->modelFactoryMock);

        $webhookHandler->onMessageCreated(
            function (MessageCreatedUpdate $update, Api $api) use (&$handlerWasCalled, $testUpdate) {
                $this->assertSame($testUpdate, $update);
                $this->assertSame($this->apiMock, $api);
                $handlerWasCalled = true;
            }
        );

        $webhookHandler->dispatch($testUpdate);

        $this->assertTrue($handlerWasCalled, 'The registered handler for onMessageCreated was not called.');
    }

    #[Test]
    public function dispatchDoesNothingForUnregisteredEvent(): void
    {
        $updateData = json_decode($this->createValidUpdatePayload(), true);
        $testUpdate = $this->createRealUpdateObject($updateData);
        $webhookHandler = new WebhookHandler($this->apiMock, $this->modelFactoryMock);

        $webhookHandler->dispatch($testUpdate);

        $this->expectNotToPerformAssertions();
    }

    #[Test]
    public function parseUpdateThrowsExceptionForEmptyPayload(): void
    {
        $this->expectException(SerializationException::class);
        $this->expectExceptionMessage('Webhook body is empty.');

        $request = new ServerRequest('POST', '/webhook', [], '');
        $webhookHandler = new WebhookHandler($this->apiMock, $this->modelFactoryMock);
        $webhookHandler->parseUpdate($request);
    }

    #[Test]
    public function parseUpdateThrowsExceptionForInvalidJson(): void
    {
        $this->expectException(SerializationException::class);
        $this->expectExceptionMessage('Failed to decode webhook body as JSON.');

        $request = new ServerRequest('POST', '/webhook', [], '{invalid-json');
        $webhookHandler = new WebhookHandler($this->apiMock, $this->modelFactoryMock);
        $webhookHandler->parseUpdate($request);
    }

    #[Test]
    public function parseUpdateThrowsExceptionForInvalidSignature(): void
    {
        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('Signature verification failed.');

        $request = new ServerRequest(
            'POST', '/webhook', ['X-Max-Bot-Api-Secret' => 'wrong-signature'], $this->createValidUpdatePayload()
        );
        $webhookHandler = new WebhookHandler($this->apiMock, $this->modelFactoryMock, self::SECRET);
        $webhookHandler->parseUpdate($request);
    }

    #[Test]
    public function signatureVerificationIsSkippedWhenNoSecretIsConfigured(): void
    {
        $payload = $this->createValidUpdatePayload();
        $updateData = json_decode($payload, true);
        $expectedUpdate = $this->createRealUpdateObject($updateData);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createUpdate')
            ->willReturn($expectedUpdate);

        $request = new ServerRequest(
            'POST', '/webhook', ['X-Max-Bot-Api-Secret' => 'any-signature-or-empty'], $payload
        );

        $webhookHandler = new WebhookHandler($this->apiMock, $this->modelFactoryMock, null);

        $result = $webhookHandler->parseUpdate($request);

        $this->assertSame($expectedUpdate, $result);
    }

    #[Test]
    public function getUpdateParsesRequestAndReturnsUpdateObject(): void
    {
        $payload = $this->createValidUpdatePayload();
        $updateData = json_decode($payload, true);
        $expectedUpdate = $this->createRealUpdateObject($updateData);

        $request = new ServerRequest('POST', '/webhook', [], $payload);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createUpdate')
            ->with($updateData)
            ->willReturn($expectedUpdate);

        $webhookHandler = new WebhookHandler($this->apiMock, $this->modelFactoryMock);
        $result = $webhookHandler->getUpdate($request);

        $this->assertSame($expectedUpdate, $result);
    }

    #[Test]
    public function handleWithoutRequestWhenGuzzleIsPresent(): void
    {
        $this->expectException(SerializationException::class);
        $this->expectExceptionMessage('Webhook body is empty.');

        $webhookHandler = new WebhookHandler($this->apiMock, $this->modelFactoryMock);
        $webhookHandler->handle(null);
    }

    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function handleWithoutRequestWhenGuzzleIsMissing(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageMatches('/No ServerRequest was provided and "guzzlehttp\/psr7" is not found/');

        $classExistsMock = $this->getFunctionMock('BushlanovDev\MaxMessengerBot', 'class_exists');

        $classExistsMock->expects($this->once())->with('GuzzleHttp\Psr7\ServerRequest')->willReturn(false);

        $webhookHandler = new WebhookHandler($this->apiMock, $this->modelFactoryMock);
        $webhookHandler->handle(null);
    }

    #[Test]
    public function verifySignatureLogsWarningOnFailure(): void
    {
        $request = new ServerRequest(
            'POST', '/webhook', ['X-Max-Bot-Api-Secret' => 'wrong-signature'], $this->createValidUpdatePayload()
        );
        $webhookHandler = new WebhookHandler($this->apiMock, $this->modelFactoryMock, self::SECRET, $this->loggerMock);

        $this->loggerMock
            ->expects($this->once())
            ->method('warning')
            ->with('Webhook signature verification failed', ['received_signature' => 'wrong-signature']);

        $this->expectException(SecurityException::class);
        $webhookHandler->parseUpdate($request);
    }
}
