<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Traits\ResponseBody;

trait ReturnString
{
    protected function handleResponseBody(string $body): string
    {
        return trim($body);
    }
}
