<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot;

use BushlanovDev\MaxMessengerBot\Exceptions\ClientApiException;
use BushlanovDev\MaxMessengerBot\Exceptions\NetworkException;
use BushlanovDev\MaxMessengerBot\Exceptions\SerializationException;
use BushlanovDev\MaxMessengerBot\Models\BotInfo;
use BushlanovDev\MaxMessengerBot\Models\ResultModel;
use BushlanovDev\MaxMessengerBot\Models\Subscription;
use BushlanovDev\MaxMessengerBot\Models\SubscriptionRequestBody;
use InvalidArgumentException;

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
    //    private const string METHOD_DELETE = 'DELETE';

    private const string ACTION_ME = '/me';
    private const string ACTION_SUBSCRIPTIONS = '/subscriptions';

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
     *
     * @throws ClientApiException
     * @throws NetworkException
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
     *
     * @throws ClientApiException
     * @throws NetworkException
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
     * @param SubscriptionRequestBody $body
     *
     * @return ResultModel
     *
     * @throws ClientApiException
     * @throws NetworkException
     * @throws SerializationException
     */
    public function subscribe(SubscriptionRequestBody $body): ResultModel
    {
        return $this->modelFactory->createResult(
            $this->client->request(
                self::METHOD_POST,
                self::ACTION_SUBSCRIPTIONS,
                [],
                $body->toArray(),
            )
        );
    }
}
