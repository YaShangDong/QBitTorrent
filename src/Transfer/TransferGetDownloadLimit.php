<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Transfer;

use YaSD\QBitTorrent\Traits\ResponseBody;
use YaSD\QBitTorrent\Traits\ResponseCode;

class TransferGetDownloadLimit extends Transfer
{
    use ResponseBody\ReturnInt;
    use ResponseCode\Code200;

    protected function getApiName(): string
    {
        return 'downloadLimit';
    }
}
