<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Transfer;

use YaSD\QBitTorrent\Traits\ResponseBody;
use YaSD\QBitTorrent\Traits\ResponseCode;

class TransferGetInfo extends Transfer
{
    use ResponseBody\ReturnJson;
    use ResponseCode\Code200;

    protected function getApiName(): string
    {
        return 'info';
    }
}
