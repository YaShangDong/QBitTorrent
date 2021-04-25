<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Torrent;

use YaSD\QBitTorrent\Exception\OperationFailedException;
use YaSD\QBitTorrent\Traits\ResponseBody;
use YaSD\QBitTorrent\Traits\ResponseCode;

class TorrentSetCategory extends Torrent
{
    use ResponseBody\DoNothing;
    use ResponseCode\Non200Codes;

    public function __construct(
        protected string $hashes,
        protected string $category
    ) {
    }

    protected function handleNon200Codes(int $code): void
    {
        if (409 === $code) {
            throw OperationFailedException::forCode409('Category name does not exist');
        }
    }

    protected function getApiName(): string
    {
        return 'setCategory';
    }
}
