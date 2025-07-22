<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot;

use BushlanovDev\MaxMessengerBot\Enums\UpdateType;
use BushlanovDev\MaxMessengerBot\Exceptions\SecurityException;
use BushlanovDev\MaxMessengerBot\Exceptions\SerializationException;
use BushlanovDev\MaxMessengerBot\Models\Updates\AbstractUpdate;
use Psr\Http\Message\ServerRequestInterface;

/**
 * A class designed to process incoming webhook requests from the Max API.
 * It verifies the request's authenticity, parses it, and dispatches it
 * to the appropriate registered event handler.
 */
final class WebhookHandler
{
    /**
     * @var array<string, callable>
     */
    private array $handlers = [];

    /**
     * @param Api $api An instance of the Api to be passed to handlers for immediate responses.
     * @param ModelFactory $modelFactory An instance of the model factory to create Update objects.
     * @param string|null $secret The secret key provided during webhook subscription to verify requests.
     */
    public function __construct(
        private readonly Api $api,
        private readonly ModelFactory $modelFactory,
        private readonly ?string $secret = null,
    ) {
    }

    /**
     * Registers a handler for a specific update type.
     *
     * @param UpdateType $type The type of update to handle.
     * @param callable $handler The function to execute when the update is received.
     *        The handler will receive the specific Update object (e.g., MessageCreatedUpdate) and the Api instance.
     *
     * @return $this
     */
    public function addHandler(UpdateType $type, callable $handler): self
    {
        $this->handlers[$type->value] = $handler;

        return $this;
    }

    /**
     * A convenient alias for addHandler(UpdateType::MessageCreated, $handler).
     *
     * @param callable(Models\Updates\MessageCreatedUpdate, Api): void $handler
     *
     * @return $this
     */
    public function onMessageCreated(callable $handler): self
    {
        return $this->addHandler(UpdateType::MessageCreated, $handler);
    }

    /**
     * A convenient alias for addHandler(UpdateType::BotStarted, $handler).
     *
     * @param callable(Models\Updates\BotStartedUpdate, Api): void $handler
     *
     * @return $this
     */
    public function onBotStarted(callable $handler): self
    {
        return $this->addHandler(UpdateType::BotStarted, $handler);
    }

    /**
     * Processes an incoming webhook request.
     * This is the main entry point. It reads the HTTP request body and headers,
     * verifies the signature, parses the update, and calls the appropriate handler.
     * It automatically sends the correct HTTP response code.
     *
     * @param ServerRequestInterface|null $request The Psr7 HTTP request to process.
     *
     * @throws \ReflectionException
     * @throws SecurityException
     * @throws SerializationException
     * @throws \LogicException
     */
    public function handle(?ServerRequestInterface $request = null): void
    {
        if ($request === null) {
            if (!class_exists(\GuzzleHttp\Psr7\ServerRequest::class)) {
                throw new \LogicException(
                    'No ServerRequest was provided and "guzzlehttp/psr7" is not found. ' .
                    'Please run "composer require guzzlehttp/psr7" or create and pass your own PSR-7 request object.',
                );
            }
            $request = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();
        }


        $update = $this->parseUpdate($request);
        $this->dispatch($update);

        http_response_code(200);
    }

    /**
     * Parses the raw request data and returns a typed Update object.
     *
     * @param ServerRequestInterface $request
     *
     * @return AbstractUpdate
     * @throws \ReflectionException
     * @throws SecurityException
     * @throws SerializationException
     * @throws \LogicException
     */
    public function parseUpdate(ServerRequestInterface $request): AbstractUpdate
    {
        $payload = (string)$request->getBody();
        $signature = $request->getHeaderLine('X-Max-Bot-Api-Secret');

        if (empty($payload)) {
            throw new SerializationException('Webhook body is empty.');
        }

        $this->verifySignature($signature);

        try {
            $data = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new SerializationException('Failed to decode webhook body as JSON.', 0, $e);
        }

        return $this->modelFactory->createUpdate($data);
    }

    /**
     * Dispatches a parsed Update object to its registered handler.
     *
     * @param AbstractUpdate $update
     */
    public function dispatch(AbstractUpdate $update): void
    {
        $handler = $this->handlers[$update->updateType->value] ?? null;

        if ($handler) {
            $handler($update, $this->api);
        }
    }

    /**
     * Verifies the 'X-Max-Bot-Api-Secret' header if a secret is configured.
     *
     * @param string $signature
     *
     * @throws SecurityException
     */
    private function verifySignature(string $signature): void
    {
        if ($this->secret === null) {
            return;
        }

        if (!hash_equals($this->secret, $signature)) {
            throw new SecurityException('Signature verification failed.');
        }
    }
}
