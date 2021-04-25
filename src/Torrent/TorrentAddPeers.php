<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Torrent;

use YaSD\QBitTorrent\Exception\InvalidArgumentException;
use YaSD\QBitTorrent\Traits\ResponseBody;
use YaSD\QBitTorrent\Traits\ResponseCode;

class TorrentAddPeers extends Torrent
{
    use ResponseBody\DoNothing;
    use ResponseCode\Non200Codes;

    public function __construct(
        protected string $hashes,
        protected string $peers
    ) {
    }

    protected function handleNon200Codes(int $code): void
    {
        if (400 === $code) {
            throw InvalidArgumentException::fromTorrentAddPeers($this->peers);
        }
    }

    protected function getApiName(): string
    {
        return 'addPeers';
    }
}
