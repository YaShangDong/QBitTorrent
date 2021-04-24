<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Exception;

use RuntimeException;
use Throwable;
use YaSD\QBitTorrent\Exception;

class LoginFailedException extends RuntimeException implements Exception
{
    public static function forCookie(?Throwable $previous = null): static
    {
        $msg = 'Login_Failed: no auth cookie';
        return new static($msg, 0, $previous);
    }
}
