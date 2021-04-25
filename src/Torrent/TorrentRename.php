<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Torrent;

use YaSD\QBitTorrent\Exception\InvalidArgumentException;
use YaSD\QBitTorrent\Exception\NotFoundException;
use YaSD\QBitTorrent\Traits\ResponseBody;
use YaSD\QBitTorrent\Traits\ResponseCode;

class TorrentRename extends Torrent
{
    use ResponseBody\DoNothing;
    use ResponseCode\Non200Codes;

    public function __construct(
        protected string $hash,
        protected string $name,
    ) {
    }

    protected function handleNon200Codes(int $code): void
    {
        if (404 === $code) {
            throw NotFoundException::forTorHash($this->hash);
        }
        if (409 === $code) {
            throw InvalidArgumentException::forTorrentName($this->name);
        }
    }

    protected function getApiName(): string
    {
        return 'rename';
    }
}
