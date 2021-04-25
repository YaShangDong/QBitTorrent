<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Torrent;

use YaSD\QBitTorrent\Traits\ResponseBody;
use YaSD\QBitTorrent\Traits\ResponseCode;

class TorrentDelete extends Torrent
{
    use ResponseBody\DoNothing;
    use ResponseCode\Code200;

    public function __construct(
        protected string $hashes,
        protected bool $deleteFiles,
    ) {
    }

    protected function getApiName(): string
    {
        return 'delete';
    }
}
