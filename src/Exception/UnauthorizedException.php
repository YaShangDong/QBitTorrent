<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Exception;

use RuntimeException;
use Throwable;
use YaSD\QBitTorrent\Exception;

class UnauthorizedException extends RuntimeException implements Exception
{
    public static function forCookie(?Throwable $previous = null): static
    {
        $msg = 'Unauthorized: no auth cookie, login first';
        return new static($msg, 0, $previous);
    }
}
