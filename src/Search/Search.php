<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Search;

use YaSD\QBitTorrent\Api;

abstract class Search extends Api
{
    protected function getApiGroup(): string
    {
        return 'search';
    }
}
