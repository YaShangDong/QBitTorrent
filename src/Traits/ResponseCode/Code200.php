<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Traits\ResponseCode;

use YaSD\QBitTorrent\Exception\UnexpectedResponseException;

trait Code200
{
    protected function handleResponseCode(int $code): void
    {
        if (200 !== $code) {
            throw UnexpectedResponseException::forUnexpectedCode($code);
        }
    }
}
