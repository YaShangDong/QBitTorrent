<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Exception;

use RuntimeException;
use Throwable;
use YaSD\QBitTorrent\Exception;

class UnexpectedResponseException extends RuntimeException implements Exception
{
    public static function forUnexpectedCode(int $code, ?Throwable $previous = null): static
    {
        $msg = sprintf('Unexpected_Response_Status_Code: %d', $code);
        return new static($msg, 0, $previous);
    }

    public static function forInvalidJson(string $json, ?Throwable $previous = null): static
    {
        $msg = sprintf('Invalid_Response_Json: %s', $json);
        return new static($msg, 0, $previous);
    }
}
