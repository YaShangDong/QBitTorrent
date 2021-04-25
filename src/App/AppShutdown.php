<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\App;

use YaSD\QBitTorrent\Traits\ResponseBody;
use YaSD\QBitTorrent\Traits\ResponseCode;

class AppShutdown extends App
{
    use ResponseBody\DoNothing;
    use ResponseCode\Code200;

    protected function getApiName(): string
    {
        return 'shutdown';
    }
}
