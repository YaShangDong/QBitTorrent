<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Sync;

use YaSD\QBitTorrent\Api;

abstract class Sync extends Api
{
    protected function getApiGroup(): string
    {
        return 'sync';
    }
}
