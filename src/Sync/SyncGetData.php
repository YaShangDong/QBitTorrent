<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Sync;

use YaSD\QBitTorrent\Traits\ResponseBody;
use YaSD\QBitTorrent\Traits\ResponseCode;

class SyncGetData extends Sync
{
    use ResponseBody\ReturnJson;
    use ResponseCode\Code200;

    public function __construct(
        protected int $rid
    ) {
    }

    protected function getApiName(): string
    {
        return 'maindata';
    }
}
