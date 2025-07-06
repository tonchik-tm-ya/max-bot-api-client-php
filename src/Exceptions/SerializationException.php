<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Exceptions;

use LogicException;
use Throwable;

class SerializationException extends LogicException
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
