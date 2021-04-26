<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Rss;

use YaSD\QBitTorrent\Traits\ResponseBody;
use YaSD\QBitTorrent\Traits\ResponseCode;

class RssGetAllItems extends Rss
{
    use ResponseBody\ReturnJson;
    use ResponseCode\Code200;

    public function __construct(
        protected ?bool $withData = null
    ) {
    }

    protected function getApiName(): string
    {
        return 'items';
    }
}
