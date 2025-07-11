<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests;

use BushlanovDev\MaxMessengerBot\Client;
use BushlanovDev\MaxMessengerBot\Exceptions\ClientApiException;
use BushlanovDev\MaxMessengerBot\Exceptions\ForbiddenException;
use BushlanovDev\MaxMessengerBot\Exceptions\MethodNotAllowedException;
use BushlanovDev\MaxMessengerBot\Exceptions\NetworkException;
use BushlanovDev\MaxMessengerBot\Exceptions\NotFoundException;
use BushlanovDev\MaxMessengerBot\Exceptions\RateLimitExceededException;
use BushlanovDev\MaxMessengerBot\Exceptions\SerializationException;
use BushlanovDev\MaxMessengerBot\Exceptions\UnauthorizedException;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

#[CoversClass(Client::class)]
final class ClientTest extends TestCase
{
    private const string FAKE_TOKEN = '12345:abcdef';
    private const string API_VERSION = '0.0.6';
    private const string API_BASE_URL = 'https://botapi.max.ru';

    private MockObject&ClientInterface $httpClientMock;
    private MockObject&RequestFactoryInterface $requestFactoryMock;
    private MockObject&StreamFactoryInterface $streamFactoryMock;
    private MockObject&RequestInterface $requestMock;
    private MockObject&ResponseInterface $responseMock;
    private MockObject&StreamInterface $streamMock;

    private Client $client;

    /**
     * This method is called before each test.
     *
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create mocks for all PSR interfaces
        $this->httpClientMock = $this->createMock(ClientInterface::class);
        $this->requestFactoryMock = $this->createMock(RequestFactoryInterface::class);
        $this->streamFactoryMock = $this->createMock(StreamFactoryInterface::class);
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->responseMock = $this->createMock(ResponseInterface::class);
        $this->streamMock = $this->createMock(StreamInterface::class);

        // Common mock setups
        $this->requestFactoryMock->method('createRequest')->willReturn($this->requestMock);
        $this->responseMock->method('getBody')->willReturn($this->streamMock);
        $this->httpClientMock->method('sendRequest')->willReturn($this->responseMock);
        $this->requestMock->method('withBody')->willReturn($this->requestMock);
        $this->requestMock->method('withHeader')->willReturn($this->requestMock);

        // Instantiate the System Under Test (SUT)
        $this->client = new Client(
            self::FAKE_TOKEN,
            $this->httpClientMock,
            $this->requestFactoryMock,
            $this->streamFactoryMock,
            self::API_VERSION,
        );
    }

    #[Test]
    public function constructorThrowsExceptionOnEmptyToken(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Access token cannot be empty.');

        new Client('', $this->httpClientMock, $this->requestFactoryMock, $this->streamFactoryMock);
    }

    #[Test]
    public function successfulGetRequest(): void
    {
        $uri = '/me';
        $expectedUrl = self::API_BASE_URL . $uri . '?' . http_build_query([
                'access_token' => self::FAKE_TOKEN,
                'v' => self::API_VERSION,
            ]);
        $responsePayload = ['id' => 987, 'name' => 'TestBot'];

        // Configure mocks for this specific test
        $this->requestFactoryMock
            ->expects($this->once())
            ->method('createRequest')
            ->with('GET', $expectedUrl)
            ->willReturn($this->requestMock);

        $this->httpClientMock
            ->expects($this->once())
            ->method('sendRequest')
            ->with($this->requestMock)
            ->willReturn($this->responseMock);

        $this->responseMock->method('getStatusCode')->willReturn(200);
        $this->streamMock->method('__toString')->willReturn(json_encode($responsePayload));

        // Execute and assert
        $result = $this->client->request('GET', $uri);
        $this->assertSame($responsePayload, $result);
    }

    #[Test]
    public function successfulPostRequestWithJsonBody(): void
    {
        $uri = '/subscriptions';
        $requestBody = [
            'subscriptions' => [
                [
                    'url' => 'https://example.com/webhook',
                    'time' => 1678886400000,
                    'update_types' => ['message_created'],
                    'version' => '0.0.1',
                ],
            ],
        ];
        $responsePayload = ['success' => true];

        $expectedUrl = self::API_BASE_URL . $uri . '?' . http_build_query([
                'access_token' => self::FAKE_TOKEN,
                'v' => self::API_VERSION
            ]);

        $this->requestFactoryMock
            ->expects($this->once())
            ->method('createRequest')
            ->with('POST', $expectedUrl)
            ->willReturn($this->requestMock);

        $this->streamFactoryMock
            ->expects($this->once())
            ->method('createStream')
            ->with(json_encode($requestBody))
            ->willReturn($this->streamMock);

        $this->requestMock
            ->expects($this->once())
            ->method('withBody')
            ->with($this->streamMock)
            ->willReturn($this->requestMock);

        $this->requestMock
            ->expects($this->once())
            ->method('withHeader')
            ->with('Content-Type', 'application/json; charset=utf-8')
            ->willReturn($this->requestMock);

        $this->responseMock->method('getStatusCode')->willReturn(200);
        $this->streamMock->method('__toString')->willReturn(json_encode($responsePayload));

        $result = $this->client->request('POST', $uri, [], $requestBody);
        $this->assertSame($responsePayload, $result);
    }

    #[Test]
    public function handlesEmptySuccessfulResponse(): void
    {
        $this->responseMock->method('getStatusCode')->willReturn(200);
        $this->streamMock->method('__toString')->willReturn('');

        $result = $this->client->request('DELETE', '/subscriptions');

        $this->assertSame(['success' => true], $result);
    }

    #[Test]
    public function throwsNetworkExceptionOnClientError(): void
    {
        $this->expectException(NetworkException::class);

        // Create a generic PSR-18 exception
        $psrException = new class extends \Exception implements ClientExceptionInterface {};

        $this->httpClientMock
            ->method('sendRequest')
            ->willThrowException($psrException);

        $this->client->request('GET', '/me');
    }

    #[Test]
    public function throwsSerializationExceptionOnInvalidJsonResponse(): void
    {
        $this->expectException(SerializationException::class);
        $this->expectExceptionMessage('Failed to decode API response JSON.');

        $this->responseMock->method('getStatusCode')->willReturn(200);
        $this->streamMock->method('__toString')->willReturn('{not-valid-json');

        $this->client->request('GET', '/me');
    }

    #[Test]
    public function throwsSerializationExceptionOnInvalidRequestBody(): void
    {
        $this->expectException(SerializationException::class);
        $this->expectExceptionMessage('Failed to encode request body to JSON.');

        // \NAN cannot be encoded in JSON
        $invalidBody = ['value' => \NAN];

        $this->client->request('POST', '/messages', [], $invalidBody);
    }

    /**
     * Data provider for testing various API error status codes.
     */
    public static function apiErrorProvider(): array
    {
        return [
            '400 Bad Request' => [400, ClientApiException::class, 'bad.request', 'Invalid parameters'],
            '401 Unauthorized' => [401, UnauthorizedException::class, 'verify.token', 'Invalid access_token'],
            '403 Forbidden' => [403, ForbiddenException::class, 'access.denied', 'You don\'t have permissions'],
            '404 Not Found' => [404, NotFoundException::class, 'not.found', 'Resource not found'],
            '405 Method Not Allowed' => [
                405,
                MethodNotAllowedException::class,
                'method.not.allowed',
                'Method not allowed',
            ],
            '429 Rate Limit' => [429, RateLimitExceededException::class, 'rate.limit', 'Rate limit exceeded'],
            '503 Service Unavailable' => [
                503,
                ClientApiException::class,
                'service.unavailable',
                'Service is temporarily unavailable',
            ],
        ];
    }

    #[Test]
    #[DataProvider('apiErrorProvider')]
    public function throwsCorrectExceptionForApiErrorStatusCodes(
        int $statusCode,
        string $exceptionClass,
        string $errorCode,
        string $errorMessage,
    ): void {
        $this->expectException($exceptionClass);
        $this->expectExceptionMessage($errorMessage);

        $errorPayload = json_encode(['code' => $errorCode, 'message' => $errorMessage]);

        $this->responseMock->method('getStatusCode')->willReturn($statusCode);
        $this->streamMock->method('__toString')->willReturn($errorPayload);

        try {
            $this->client->request('GET', '/some/failing/endpoint');
        } catch (ClientApiException $e) {
            // Also assert the specific properties of our custom exception
            $this->assertSame($statusCode, $e->getHttpStatusCode());
            $this->assertSame($errorCode, $e->errorCode);
            $this->assertSame($this->responseMock, $e->response);
            throw $e; // Re-throw for PHPUnit to catch the expected exception type
        }
    }
}
