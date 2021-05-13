<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\App;

use YaSD\QBitTorrent\Exception\UnauthorizedException;
use YaSD\QBitTorrent\Traits\ResponseBody;
use YaSD\QBitTorrent\Traits\ResponseCode;

class ApiGetVersion extends App
{
    use ResponseBody\ReturnString;
    use ResponseCode\Non200Codes;

    protected function getApiName(): string
    {
        return 'webapiVersion';
    }

    protected function handleNon200Codes(int $code): void
    {
        if (403 === $code) {
            throw UnauthorizedException::for403();
        }
    }
}
