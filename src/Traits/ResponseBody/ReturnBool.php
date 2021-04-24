<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Traits\ResponseBody;

trait ReturnBool
{
    protected function handleResponseBody(string $body): bool
    {
        return (bool) trim($body);
    }
}
