<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Traits\ResponseBody;

use Throwable;
use YaSD\QBitTorrent\Exception\UnexpectedResponseException;

trait ReturnJson
{
    protected function handleResponseBody(string $body): array
    {
        try {
            return json_decode(trim($body), true, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            throw UnexpectedResponseException::forInvalidJson($body);
        }
    }
}
