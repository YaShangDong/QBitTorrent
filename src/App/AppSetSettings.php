<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\App;

use YaSD\QBitTorrent\Traits\ResponseBody;
use YaSD\QBitTorrent\Traits\ResponseCode;

class AppSetSettings extends App
{
    use ResponseBody\DoNothing;
    use ResponseCode\Code200;

    public function __construct(
        protected string $json,
    ) {
    }

    protected function getApiName(): string
    {
        return 'setPreferences';
    }
}
