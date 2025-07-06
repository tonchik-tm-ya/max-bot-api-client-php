<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Exceptions;

use RuntimeException;
use Throwable;

class NetworkException extends RuntimeException
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
