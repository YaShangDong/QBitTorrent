<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Transfer;

use YaSD\QBitTorrent\Api;

abstract class Transfer extends Api
{
    protected function getApiGroup(): string
    {
        return 'transfer';
    }
}
