<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Torrent;

use YaSD\QBitTorrent\Exception\NotFoundException;
use YaSD\QBitTorrent\Traits\ResponseBody;
use YaSD\QBitTorrent\Traits\ResponseCode;

class TorrentGetTrackers extends Torrent
{
    use ResponseBody\ReturnJson;
    use ResponseCode\Non200Codes;

    public function __construct(
        protected string $hash
    ) {
    }

    protected function handleNon200Codes(int $code): void
    {
        if (404 === $code) {
            throw NotFoundException::forTorHash($this->hash);
        }
    }

    protected function getApiName(): string
    {
        return 'trackers';
    }
}
