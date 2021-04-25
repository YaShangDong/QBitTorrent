<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Log;

use YaSD\QBitTorrent\Traits\ResponseBody;
use YaSD\QBitTorrent\Traits\ResponseCode;

class LogGetLogs extends Log
{
    use ResponseBody\ReturnJson;
    use ResponseCode\Code200;

    public function __construct(
        protected bool $normal,
        protected bool $info,
        protected bool $warning,
        protected bool $critical,
        protected int $last_known_id,
    ) {
    }

    protected function getApiName(): string
    {
        return 'main';
    }
}
