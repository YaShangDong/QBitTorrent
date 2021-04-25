<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Torrent;

use YaSD\QBitTorrent\Exception\OperationFailedException;
use YaSD\QBitTorrent\Traits\ResponseBody;
use YaSD\QBitTorrent\Traits\ResponseCode;

class TorrentIncreasePriority extends Torrent
{
    use ResponseBody\DoNothing;
    use ResponseCode\Non200Codes;

    public function __construct(
        protected string $hashes
    ) {
    }

    protected function handleNon200Codes(int $code): void
    {
        if (409 === $code) {
            throw OperationFailedException::forTorrentPriority();
        }
    }

    protected function getApiName(): string
    {
        return 'increasePrio';
    }
}
