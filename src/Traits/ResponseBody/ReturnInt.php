<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Traits\ResponseBody;

trait ReturnInt
{
    protected function handleResponseBody(string $body): int
    {
        return (int) trim($body);
    }
}
