<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Auth;

use YaSD\QBitTorrent\Api;

abstract class Auth extends Api
{
    protected function getApiGroup(): string
    {
        return 'auth';
    }
}
