<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Rss;

use YaSD\QBitTorrent\Traits\ResponseBody;
use YaSD\QBitTorrent\Traits\ResponseCode;

class RssGetAllRules extends Rss
{
    use ResponseBody\ReturnJson;
    use ResponseCode\Code200;

    protected function getApiName(): string
    {
        return 'rules';
    }
}
