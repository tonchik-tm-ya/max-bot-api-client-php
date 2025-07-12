<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot;

use BushlanovDev\MaxMessengerBot\Exceptions\ForbiddenException;
use BushlanovDev\MaxMessengerBot\Exceptions\ClientApiException;
use BushlanovDev\MaxMessengerBot\Exceptions\MethodNotAllowedException;
use BushlanovDev\MaxMessengerBot\Exceptions\NetworkException;
use BushlanovDev\MaxMessengerBot\Exceptions\NotFoundException;
use BushlanovDev\MaxMessengerBot\Exceptions\RateLimitExceededException;
use BushlanovDev\MaxMessengerBot\Exceptions\SerializationException;
use BushlanovDev\MaxMessengerBot\Exceptions\UnauthorizedException;
use InvalidArgumentException;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * The low-level HTTP client responsible for communicating with the Max Bot API.
 * It handles request signing, error handling, and JSON serialization/deserialization.
 * This class is an abstraction over any PSR-18 compatible HTTP client.
 */
final class Client implements ClientApiInterface
{
    private const string API_BASE_URL = 'https://botapi.max.ru';

    private const string DEFAULT_API_VERSION = '0.0.6';

    /**
     * @param string $accessToken Your bot's access token from @MasterBot.
     * @param ClientInterface $httpClient A PSR-18 compatible HTTP client (e.g., Guzzle).
     * @param RequestFactoryInterface $requestFactory A PSR-17 factory for creating requests.
     * @param StreamFactoryInterface $streamFactory A PSR-17 factory for creating request body streams.
     * @param string $apiVersion The API version to use for requests.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        private readonly string $accessToken,
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly string $apiVersion = self::DEFAULT_API_VERSION,
    ) {
        if (empty($accessToken)) {
            throw new InvalidArgumentException('Access token cannot be empty.');
        }
    }

    /**
     * @inheritDoc
     */
    public function request(string $method, string $uri, array $queryParams = [], array $body = []): array
    {
        $queryParams['access_token'] = $this->accessToken;
        $queryParams['v'] = $this->apiVersion;

        $fullUrl = self::API_BASE_URL . $uri . '?' . http_build_query($queryParams);
        $request = $this->requestFactory->createRequest($method, $fullUrl);

        if (!empty($body)) {
            try {
                $payload = json_encode($body, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                throw new SerializationException('Failed to encode request body to JSON.', 0, $e);
            }
            $stream = $this->streamFactory->createStream($payload);
            $request = $request
                ->withBody($stream)
                ->withHeader('Content-Type', 'application/json; charset=utf-8');
        }

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            // This catches network errors, DNS failures, timeouts, etc.
            throw new NetworkException($e->getMessage(), $e->getCode(), $e);
        }

        $this->handleErrorResponse($response);

        $responseBody = (string)$response->getBody();

        // Handle successful but empty responses (e.g., from DELETE endpoints)
        if (empty($responseBody)) {
            // The API spec often returns {"success": true}, so we can simulate that
            // for consistency if the body is truly empty.
            return ['success' => true];
        }

        try {
            return json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new SerializationException('Failed to decode API response JSON.', 0, $e);
        }
    }

    /**
     * Checks the response for an error status code and throws a corresponding typed exception.
     *
     * @throws ClientApiException
     */
    private function handleErrorResponse(ResponseInterface $response): void
    {
        $statusCode = $response->getStatusCode();

        // 2xx codes are considered successful.
        if ($statusCode >= 200 && $statusCode < 300) {
            return;
        }

        $responseBody = (string)$response->getBody();
        $data = json_decode($responseBody, true) ?? [];
        $errorCode = $data['code'] ?? 'unknown';
        $errorMessage = $data['message'] ?? 'An unknown error occurred.';

        throw match ($statusCode) {
            401 => new UnauthorizedException($errorMessage, $errorCode, $response),
            403 => new ForbiddenException($errorMessage, $errorCode, $response),
            404 => new NotFoundException($errorMessage, $errorCode, $response),
            405 => new MethodNotAllowedException($errorMessage, $errorCode, $response),
            429 => new RateLimitExceededException($errorMessage, $errorCode, $response),
            default => new ClientApiException($errorMessage, $errorCode, $response, $statusCode),
        };
    }
}
