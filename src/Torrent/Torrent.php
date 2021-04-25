<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Torrent;

use YaSD\QBitTorrent\Api;

abstract class Torrent extends Api
{
    protected function getApiGroup(): string
    {
        return 'torrents';
    }
}
