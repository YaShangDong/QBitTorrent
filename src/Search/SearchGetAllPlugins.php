<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Search;

use YaSD\QBitTorrent\Traits\ResponseBody;
use YaSD\QBitTorrent\Traits\ResponseCode;

class SearchGetAllPlugins extends Search
{
    use ResponseBody\ReturnJson;
    use ResponseCode\Code200;

    protected function getApiName(): string
    {
        return 'plugins';
    }
}
