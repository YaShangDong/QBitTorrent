<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Transfer;

use YaSD\QBitTorrent\Traits\ResponseBody;
use YaSD\QBitTorrent\Traits\ResponseCode;

class TransferToggleMode extends Transfer
{
    use ResponseBody\DoNothing;
    use ResponseCode\Code200;

    protected function getApiName(): string
    {
        return 'toggleSpeedLimitsMode';
    }
}
