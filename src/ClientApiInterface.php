<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot;

use BushlanovDev\MaxMessengerBot\Exceptions\ClientApiException;
use BushlanovDev\MaxMessengerBot\Exceptions\NetworkException;
use BushlanovDev\MaxMessengerBot\Exceptions\SerializationException;

interface ClientApiInterface
{
    /**
     * Performs a request to the Max Bot API.
     *
     * @param string $method The HTTP method (GET, POST, PATCH, etc.).
     * @param string $uri The API endpoint (e.g., '/me', '/messages').
     * @param array<string, mixed> $queryParams Query parameters for the request.
     * @param array<string, mixed> $body The request body.
     *
     * @return array<string, mixed> The decoded JSON response as an associative array.
     * @throws ClientApiException for API-level errors (4xx, 5xx).
     * @throws NetworkException for network-related issues.
     * @throws SerializationException for JSON encoding/decoding failures.
     */
    public function request(string $method, string $uri, array $queryParams = [], array $body = []): array;

    /**
     * Performs a file upload at the specified URL.
     *
     * @param string $uri URL received from the download API.
     * @param resource|string $fileContents File content (stream resource or string).
     * @param string $fileName The name of the file that will be sent to the server.
     *
     * @return array<string, mixed>
     * @throws ClientApiException
     * @throws NetworkException
     * @throws SerializationException
     */
    public function upload(string $uri, mixed $fileContents, string $fileName): array;
}
