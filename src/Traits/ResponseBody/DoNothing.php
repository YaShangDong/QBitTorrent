<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Traits\ResponseBody;

trait DoNothing
{
    protected function handleResponseBody(string $body): void
    {
    }
}
