<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\App;

use YaSD\QBitTorrent\Api;

abstract class App extends Api
{
    protected function getApiGroup(): string
    {
        return 'app';
    }
}
