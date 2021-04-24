<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Traits\ResponseCode;

use YaSD\QBitTorrent\Exception\UnexpectedResponseException;

trait Non200Codes
{
    protected function handleResponseCode(int $code): void
    {
        $this->handleNon200Codes($code);
        if (200 !== $code) {
            throw UnexpectedResponseException::forUnexpectedCode($code);
        }
    }

    abstract protected function handleNon200Codes(int $code): void;
}
