<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Torrent;

use YaSD\QBitTorrent\Exception\InvalidArgumentException;
use YaSD\QBitTorrent\Exception\NotFoundException;
use YaSD\QBitTorrent\Exception\OperationFailedException;
use YaSD\QBitTorrent\Traits\ResponseBody;
use YaSD\QBitTorrent\Traits\ResponseCode;

class TorrentEditTracker extends Torrent
{
    use ResponseBody\DoNothing;
    use ResponseCode\Non200Codes;

    public function __construct(
        protected string $hash,
        protected string $origUrl,
        protected string $newUrl
    ) {
    }

    protected function handleNon200Codes(int $code): void
    {
        if (404 === $code) {
            throw NotFoundException::forTorHash($this->hash);
        }
        if (409 === $code) {
            throw OperationFailedException::forCode409('newUrl already exists for the torrent or origUrl was not found');
        }
        if (400 === $code) {
            throw InvalidArgumentException::forInvalidUrl($this->newUrl);
        }
    }

    protected function getApiName(): string
    {
        return 'editTracker';
    }
}
