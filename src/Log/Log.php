<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Log;

use YaSD\QBitTorrent\Api;

abstract class Log extends Api
{
    protected function getApiGroup(): string
    {
        return 'log';
    }
}
