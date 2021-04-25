<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Search;

use YaSD\QBitTorrent\Traits\ResponseBody;
use YaSD\QBitTorrent\Traits\ResponseCode;

class SearchUninstallPlugin extends Search
{
    use ResponseBody\DoNothing;
    use ResponseCode\Code200;

    public function __construct(
        protected string $names
    ) {
    }

    protected function getApiName(): string
    {
        return 'uninstallPlugin';
    }
}
