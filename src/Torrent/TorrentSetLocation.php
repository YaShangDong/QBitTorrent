<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Torrent;

use YaSD\QBitTorrent\Exception\InvalidArgumentException;
use YaSD\QBitTorrent\Exception\OperationFailedException;
use YaSD\QBitTorrent\Traits\ResponseBody;
use YaSD\QBitTorrent\Traits\ResponseCode;

class TorrentSetLocation extends Torrent
{
    use ResponseBody\DoNothing;
    use ResponseCode\Non200Codes;

    public function __construct(
        protected string $hashes,
        protected string $location
    ) {
    }

    protected function handleNon200Codes(int $code): void
    {
        if (400 === $code) {
            throw InvalidArgumentException::forSavePath($this->location);
        }
        if (409 === $code) {
            throw OperationFailedException::forCode409('Unable to create save path directory');
        }
        if (403 === $code) {
            throw new OperationFailedException('Set_Torrent_Location: User does not have write access to directory');
        }
    }

    protected function getApiName(): string
    {
        return 'setLocation';
    }
}
