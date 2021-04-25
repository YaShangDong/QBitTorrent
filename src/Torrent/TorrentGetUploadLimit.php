<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Torrent;

use YaSD\QBitTorrent\Traits\ResponseBody;
use YaSD\QBitTorrent\Traits\ResponseCode;

class TorrentGetUploadLimit extends Torrent
{
    use ResponseBody\ReturnJson;
    use ResponseCode\Code200;

    public function __construct(
        protected string $hashes
    ) {
    }

    protected function getApiName(): string
    {
        return 'uploadLimit';
    }
}
