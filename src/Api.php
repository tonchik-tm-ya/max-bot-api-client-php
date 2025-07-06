<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot;

use BushlanovDev\MaxMessengerBot\Models\BotInfo;

/**
 * The main entry point for interacting with the Max Bot API.
 * This class provides a clean, object-oriented interface over the raw HTTP API.
 *
 * @see https://dev.max.ru
 */
class Api
{
    private const string METHOD_GET = 'GET';

    //    private const string METHOD_POST = 'POST';

    private const string ACTION_ME = '/me';

    private readonly ClientApiInterface $client;

    private readonly ModelFactory $modelFactory;

    /**
     * Api constructor.
     *
     * @param string $accessToken Your bot's access token from @MasterBot.
     * @param ClientApiInterface|null $client Http api client.
     * @param ModelFactory|null $modelFactory
     */
    public function __construct(string $accessToken, ?ClientApiInterface $client = null, ?ModelFactory $modelFactory = null)
    {
        $this->client = $client ?? new Client(
            $accessToken,
            new \GuzzleHttp\Client(),
            new \GuzzleHttp\Psr7\HttpFactory(),
            new \GuzzleHttp\Psr7\HttpFactory(),
        );
        $this->modelFactory = $modelFactory ?? new ModelFactory();
    }

    /**
     * Returns information about the current bot, identified by an access token.
     *
     * @return BotInfo
     */
    public function getBotInfo(): BotInfo
    {
        return $this->modelFactory->createBotInfo(
            $this->client->request(self::METHOD_GET, self::ACTION_ME)
        );
    }
}
