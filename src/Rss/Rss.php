<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Rss;

use YaSD\QBitTorrent\Api;

abstract class RSS extends Api
{
    protected function getApiGroup(): string
    {
        return 'rss';
    }
}
