<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Transfer;

use YaSD\QBitTorrent\Traits\ResponseBody;
use YaSD\QBitTorrent\Traits\ResponseCode;

class TransferSetDownloadLimit extends Transfer
{
    use ResponseBody\DoNothing;
    use ResponseCode\Code200;

    public function __construct(
        protected int $limit
    ) {
    }

    protected function getApiName(): string
    {
        return 'setDownloadLimit';
    }
}
