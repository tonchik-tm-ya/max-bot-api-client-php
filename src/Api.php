<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot;

use BushlanovDev\MaxMessengerBot\Enums\MessageFormat;
use BushlanovDev\MaxMessengerBot\Enums\UpdateType;
use BushlanovDev\MaxMessengerBot\Enums\UploadType;
use BushlanovDev\MaxMessengerBot\Exceptions\ClientApiException;
use BushlanovDev\MaxMessengerBot\Exceptions\NetworkException;
use BushlanovDev\MaxMessengerBot\Exceptions\SecurityException;
use BushlanovDev\MaxMessengerBot\Exceptions\SerializationException;
use BushlanovDev\MaxMessengerBot\Models\AbstractModel;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Requests\AbstractAttachmentRequest;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Requests\AudioAttachmentRequest;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Requests\FileAttachmentRequest;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Requests\PhotoAttachmentRequest;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Requests\VideoAttachmentRequest;
use BushlanovDev\MaxMessengerBot\Models\BotInfo;
use BushlanovDev\MaxMessengerBot\Models\Chat;
use BushlanovDev\MaxMessengerBot\Models\Message;
use BushlanovDev\MaxMessengerBot\Models\MessageLink;
use BushlanovDev\MaxMessengerBot\Models\Result;
use BushlanovDev\MaxMessengerBot\Models\Subscription;
use BushlanovDev\MaxMessengerBot\Models\UpdateList;
use BushlanovDev\MaxMessengerBot\Models\Updates\AbstractUpdate;
use BushlanovDev\MaxMessengerBot\Models\UploadEndpoint;
use InvalidArgumentException;
use LogicException;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;
use RuntimeException;

/**
 * The main entry point for interacting with the Max Bot API.
 * This class provides a clean, object-oriented interface over the raw HTTP API.
 *
 * @see https://dev.max.ru
 */
class Api
{
    private const string API_BASE_URL = 'https://botapi.max.ru';
    public const string API_VERSION = '0.0.6';

    private const string METHOD_GET = 'GET';
    private const string METHOD_POST = 'POST';
    private const string METHOD_DELETE = 'DELETE';

    private const string ACTION_ME = '/me';
    private const string ACTION_SUBSCRIPTIONS = '/subscriptions';
    private const string ACTION_MESSAGES = '/messages';
    private const string ACTION_UPLOADS = '/uploads';
    private const string ACTION_CHATS = '/chats';
    private const string ACTION_UPDATES = '/updates';

    private readonly ClientApiInterface $client;

    private readonly ModelFactory $modelFactory;

    /**
     * Api constructor.
     *
     * @param string $accessToken Your bot's access token from @MasterBot.
     * @param ClientApiInterface|null $client Http api client.
     * @param ModelFactory|null $modelFactory
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        string $accessToken,
        ?ClientApiInterface $client = null,
        ?ModelFactory $modelFactory = null
    ) {
        if ($client === null) {
            if (!class_exists(\GuzzleHttp\Client::class) || !class_exists(\GuzzleHttp\Psr7\HttpFactory::class)) {
                throw new LogicException(
                    'No client was provided and "guzzlehttp/guzzle" is not found. ' .
                    'Please run "composer require guzzlehttp/guzzle" or create and pass your own implementation of ClientApiInterface.'
                );
            }

            $guzzle = new \GuzzleHttp\Client();
            $httpFactory = new \GuzzleHttp\Psr7\HttpFactory();
            $client = new Client(
                $accessToken,
                $guzzle,
                $httpFactory,
                $httpFactory,
                self::API_BASE_URL,
                self::API_VERSION,
            );
        }

        $this->client = $client;
        $this->modelFactory = $modelFactory ?? new ModelFactory();
    }

    /**
     * Creates a WebhookHandler instance, pre-configured with the necessary dependencies.
     *
     * @param string|null $secret The secret key for request verification.
     *        Should be the same one you used when calling the subscribe() method.
     *
     * @return WebhookHandler
     */
    public function createWebhookHandler(?string $secret = null): WebhookHandler
    {
        return new WebhookHandler($this, $this->modelFactory, $secret);
    }

    /**
     * Parses an incoming webhook request and returns a single Update object.
     * This is an alternative to the event-driven WebhookHandler::handle() method,
     * allowing for manual processing of updates.
     *
     * @param string|null $secret The secret key to verify the request signature.
     * @param ServerRequestInterface|null $request The PSR-7 request object. If null, it's created from globals.
     *
     * @return AbstractUpdate The parsed update object (e.g., MessageCreatedUpdate).
     * @throws \ReflectionException
     * @throws SecurityException
     * @throws SerializationException
     * @throws \LogicException
     */
    public function getWebhookUpdate(?string $secret = null, ?ServerRequestInterface $request = null): AbstractUpdate
    {
        return $this->createWebhookHandler($secret)->getUpdate($request);
    }

    /**
     * A simple way to process a single incoming webhook request using callbacks.
     * This method creates a WebhookHandler, registers the provided callbacks, and processes the request.
     *
     * @param array<string, callable> $handlers An associative array where keys are UpdateType string values
     *        (e.g., UpdateType::MessageCreated->value) and values are handlers.
     * @param string|null $secret The secret key for request verification.
     * @param ServerRequestInterface|null $request The PSR-7 request object.
     *
     * @throws SecurityException
     * @throws SerializationException
     * @throws ReflectionException
     * @throws LogicException
     */
    public function handleWebhooks(
        array $handlers,
        ?string $secret = null,
        ?ServerRequestInterface $request = null,
    ): void {
        $webhookHandler = $this->createWebhookHandler($secret);

        foreach ($handlers as $updateType => $callback) {
            $updateType = UpdateType::tryFrom($updateType);
            // @phpstan-ignore-next-line
            if ($updateType && is_callable($callback)) {
                $webhookHandler->addHandler($updateType, $callback);
            }
        }

        $webhookHandler->handle($request);
    }

    /**
     * You can use this method for getting updates in case your bot is not subscribed to WebHook.
     * The method is based on long polling.
     *
     * @param int|null $limit Maximum number of updates to be retrieved (1-1000).
     * @param int|null $timeout Timeout in seconds for long polling (0-90).
     * @param int|null $marker Pass `null` to get updates you didn't get yet.
     * @param UpdateType[]|null $types Comma separated list of update types your bot want to receive.
     *
     * @return UpdateList
     * @throws ClientApiException
     * @throws NetworkException
     * @throws ReflectionException
     * @throws SerializationException
     */
    public function getUpdates(
        ?int $limit = null,
        ?int $timeout = null,
        ?int $marker = null,
        ?array $types = null,
    ): UpdateList {
        $query = [
            'limit' => $limit,
            'timeout' => $timeout,
            'marker' => $marker,
            'types' => $types !== null ? implode(',', array_map(fn($type) => $type->value, $types)) : null,
        ];

        return $this->modelFactory->createUpdateList(
            $this->client->request(
                self::METHOD_GET,
                self::ACTION_UPDATES,
                array_filter($query, fn($value) => $value !== null),
            )
        );
    }

    /**
     * Starts a long-polling loop to process updates using callbacks.
     * This method will run indefinitely until the script is terminated.
     *
     * @param array<string, callable> $handlers An associative array where keys are UpdateType enums
     *        and values are the corresponding handler functions.
     * @param int|null $timeout Timeout in seconds for long polling (0-90). Defaults to 90.
     * @param int|null $marker Pass `null` to get updates you didn't get yet.
     */
    public function handleUpdates(array $handlers, ?int $timeout = null, ?int $marker = null): void
    {
        // @phpstan-ignore-next-line
        while (true) {
            try {
                $this->processUpdatesBatch($handlers, $timeout, $marker);
            } catch (NetworkException $e) {
                error_log("Network error: " . $e->getMessage());
                sleep(5);
            } catch (\Exception $e) {
                error_log("An error occurred: " . $e->getMessage());
                sleep(1);
            }
        }
    }

    /**
     * Processes a single batch of updates. This is the core logic used by handleUpdates().
     * Useful for custom loop implementations or for testing.
     *
     * @param array<string, callable> $handlers An associative array of update handlers.
     * @param int|null $timeout Timeout for the getUpdates call.
     * @param int|null $marker The marker for which updates to fetch.
     *
     * @throws ClientApiException
     * @throws NetworkException
     * @throws ReflectionException
     * @throws SerializationException
     */
    public function processUpdatesBatch(array $handlers, ?int $timeout, ?int &$marker = null): void
    {
        $updateList = $this->getUpdates(timeout: $timeout, marker: $marker);

        foreach ($updateList->updates as $update) {
            $handler = $handlers[$update->updateType->value] ?? null;
            if ($handler) {
                $handler($update, $this);
            }
        }

        $marker = $updateList->marker;
    }

    /**
     * Information about the current bot, identified by an access token.
     *
     * @return BotInfo
     * @throws ClientApiException
     * @throws NetworkException
     * @throws ReflectionException
     * @throws SerializationException
     */
    public function getBotInfo(): BotInfo
    {
        return $this->modelFactory->createBotInfo(
            $this->client->request(self::METHOD_GET, self::ACTION_ME)
        );
    }

    /**
     * List of all active webhook subscriptions.
     *
     * @return Subscription[]
     * @throws ClientApiException
     * @throws NetworkException
     * @throws ReflectionException
     * @throws SerializationException
     */
    public function getSubscriptions(): array
    {
        return $this->modelFactory->createSubscriptions(
            $this->client->request(self::METHOD_GET, self::ACTION_SUBSCRIPTIONS)
        );
    }

    /**
     * Subscribes the bot to receive updates via WebHook.
     *
     * @param string $url URL webhook.
     * @param string|null $secret Secret key for verifying the authenticity of requests.
     * @param UpdateType[]|null $updateTypes List of update types.
     *
     * @return Result
     * @throws ClientApiException
     * @throws NetworkException
     * @throws ReflectionException
     * @throws SerializationException
     */
    public function subscribe(
        string $url,
        ?string $secret = null,
        ?array $updateTypes = null,
    ): Result {
        return $this->modelFactory->createResult(
            $this->client->request(
                self::METHOD_POST,
                self::ACTION_SUBSCRIPTIONS,
                [],
                [
                    'url' => $url,
                    'secret' => $secret,
                    'update_types' => !empty($updateTypes) ? array_map(fn($type) => $type->value, $updateTypes) : null,
                ]
            )
        );
    }

    /**
     * Unsubscribes bot from receiving updates via WebHook.
     *
     * @param string $url URL webhook.
     *
     * @return Result
     * @throws ClientApiException
     * @throws NetworkException
     * @throws ReflectionException
     * @throws SerializationException
     */
    public function unsubscribe(string $url): Result
    {
        return $this->modelFactory->createResult(
            $this->client->request(
                self::METHOD_DELETE,
                self::ACTION_SUBSCRIPTIONS,
                compact('url'),
            )
        );
    }

    /**
     * Sends a message to a chat.
     *
     * @param int|null $userId Fill this parameter if you want to send message to user.
     * @param int|null $chatId Fill this if you send message to chat.
     * @param string|null $text Message text.
     * @param AbstractAttachmentRequest[]|null $attachments Message attachments.
     * @param MessageFormat|null $format Message format.
     * @param MessageLink|null $link Link to message.
     * @param bool $notify If false, chat participants would not be notified.
     * @param bool $disableLinkPreview If false, server will not generate media preview for links in text.
     *
     * @return Message
     * @throws ClientApiException
     * @throws NetworkException
     * @throws ReflectionException
     * @throws SerializationException
     */
    public function sendMessage(
        ?int $userId = null,
        ?int $chatId = null,
        ?string $text = null,
        ?array $attachments = null,
        ?MessageFormat $format = null,
        ?MessageLink $link = null,
        bool $notify = true,
        bool $disableLinkPreview = false,
    ): Message {
        $query = [
            'user_id' => $userId,
            'chat_id' => $chatId,
            'disable_link_preview' => $disableLinkPreview,
        ];

        $body = [
            'text' => $text,
            'format' => $format?->value,
            'notify' => $notify,
            'link' => $link,
            'attachments' => $attachments !== null ? array_map(
                fn(AbstractModel $attachment) => $attachment->toArray(),
                $attachments,
            ) : null,
        ];

        $response = $this->client->request(
            self::METHOD_POST,
            self::ACTION_MESSAGES,
            array_filter($query, fn($item) => null !== $item),
            array_filter($body, fn($item) => null !== $item),
        );

        return $this->modelFactory->createMessage($response['message']);
    }

    /**
     * Returns the URL for the subsequent file upload.
     *
     * @param UploadType $type Uploaded file type.
     *
     * @return UploadEndpoint Endpoint you should upload to your binaries.
     * @throws ReflectionException
     */
    public function getUploadUrl(UploadType $type): UploadEndpoint
    {
        return $this->modelFactory->createUploadEndpoint(
            $this->client->request(
                self::METHOD_POST,
                self::ACTION_UPLOADS,
                ['type' => $type->value],
            )
        );
    }

    /**
     * A simplified method for uploading a file and getting the resulting attachment object.
     *
     * @param UploadType $type Uploaded file type.
     * @param string $filePath Path to the file on the local disk.
     *
     * @return AbstractAttachmentRequest
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws LogicException
     * @throws ClientApiException
     * @throws NetworkException
     * @throws ReflectionException
     * @throws SerializationException
     */
    public function uploadAttachment(UploadType $type, string $filePath): AbstractAttachmentRequest
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new InvalidArgumentException("File not found or not readable: $filePath");
        }

        $fileHandle = @fopen($filePath, 'r');
        if ($fileHandle === false) {
            throw new RuntimeException("Could not open file for reading: $filePath");
        }

        $uploadEndpoint = $this->getUploadUrl($type);

        $uploadResult = $this->client->upload(
            $uploadEndpoint->url,
            $fileHandle,
            basename($filePath),
        );

        fclose($fileHandle);

        if (!isset($uploadResult['token'])) {
            throw new SerializationException('Could not find "token" in upload server response.');
        }

        return match ($type) {
            UploadType::Image => PhotoAttachmentRequest::fromToken($uploadResult['token']),
            UploadType::Video => new VideoAttachmentRequest($uploadResult['token']),
            UploadType::Audio => new AudioAttachmentRequest($uploadResult['token']),
            UploadType::File => new FileAttachmentRequest($uploadResult['token']), // @phpstan-ignore-line
            default => throw new LogicException(
                "Attachment creation for type '$type->value' is not yet implemented."
            ),
        };
    }

    /**
     * Returns info about chat.
     *
     * @param int $chatId Requested chat identifier.
     *
     * @return Chat
     * @throws ClientApiException
     * @throws NetworkException
     * @throws ReflectionException
     * @throws SerializationException
     */
    public function getChat(int $chatId): Chat
    {
        return $this->modelFactory->createChat(
            $this->client->request(self::METHOD_GET, self::ACTION_CHATS . '/' . $chatId)
        );
    }
}
