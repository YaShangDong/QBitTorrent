<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Rss;

use YaSD\QBitTorrent\Traits\ResponseBody;
use YaSD\QBitTorrent\Traits\ResponseCode;

class RssSetRule extends Rss
{
    use ResponseBody\DoNothing;
    use ResponseCode\Code200;

    public function __construct(
        protected string $ruleName,
        protected string $ruleDef,
    ) {
    }

    protected function getApiName(): string
    {
        return 'setRule';
    }
}
