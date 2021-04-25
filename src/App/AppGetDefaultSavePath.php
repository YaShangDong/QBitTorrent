<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\App;

use YaSD\QBitTorrent\Traits\ResponseBody;
use YaSD\QBitTorrent\Traits\ResponseCode;

class AppGetDefaultSavePath extends App
{
    use ResponseBody\ReturnString;
    use ResponseCode\Code200;

    protected function getApiName(): string
    {
        return 'defaultSavePath';
    }
}
