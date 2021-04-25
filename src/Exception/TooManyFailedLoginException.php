<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Exception;

use RuntimeException;
use Throwable;
use YaSD\QBitTorrent\Exception;

class TooManyFailedLoginException extends RuntimeException implements Exception
{
    public static function fromLogin(?Throwable $previous = null): static
    {
        $msg = "Too_Many_Failed_Login_Attempts: user's IP is banned, try later";
        return new static($msg, 0, $previous);
    }
}
