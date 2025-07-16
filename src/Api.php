<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot;

use BushlanovDev\MaxMessengerBot\Enums\MessageFormat;
use BushlanovDev\MaxMessengerBot\Enums\UpdateType;
use BushlanovDev\MaxMessengerBot\Exceptions\ClientApiException;
use BushlanovDev\MaxMessengerBot\Exceptions\NetworkException;
use BushlanovDev\MaxMessengerBot\Exceptions\SerializationException;
use BushlanovDev\MaxMessengerBot\Models\AbstractModel;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Requests\AbstractAttachmentRequest;
use BushlanovDev\MaxMessengerBot\Models\BotInfo;
use BushlanovDev\MaxMessengerBot\Models\Message;
use BushlanovDev\MaxMessengerBot\Models\Result;
use BushlanovDev\MaxMessengerBot\Models\Subscription;
use InvalidArgumentException;
use ReflectionException;

/**
 * The main entry point for interacting with the Max Bot API.
 * This class provides a clean, object-oriented interface over the raw HTTP API.
 *
 * @see https://dev.max.ru
 */
class Api
{
    private const string METHOD_GET = 'GET';
    private const string METHOD_POST = 'POST';
    private const string METHOD_DELETE = 'DELETE';

    private const string ACTION_ME = '/me';
    private const string ACTION_SUBSCRIPTIONS = '/subscriptions';
    private const string ACTION_MESSAGES = '/messages';

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
        $this->client = $client ?? new Client(
            $accessToken,
            new \GuzzleHttp\Client(),
            new \GuzzleHttp\Psr7\HttpFactory(),
            new \GuzzleHttp\Psr7\HttpFactory(),
        );
        $this->modelFactory = $modelFactory ?? new ModelFactory();
    }

    /**
     * Information about the current bot, identified by an access token.
     *
     * @return BotInfo
     * @throws ClientApiException
     * @throws NetworkException
     * @throws SerializationException
     * @throws ReflectionException
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
     * @throws SerializationException
     * @throws ReflectionException
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
     * @throws SerializationException
     * @throws ReflectionException
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
     * @throws SerializationException
     * @throws ReflectionException
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
     * @param MessageFormat|null $format
     * @param bool $notify If false, chat participants would not be notified.
     * @param bool $disableLinkPreview If false, server will not generate media preview for links in text.
     *
     * @return Message
     * @throws ReflectionException
     */
    public function sendMessage(
        ?int $userId = null,
        ?int $chatId = null,
        ?string $text = null,
        ?array $attachments = null,
        ?MessageFormat $format = null,
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
}
