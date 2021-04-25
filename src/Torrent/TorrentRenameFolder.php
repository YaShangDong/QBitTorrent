<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Torrent;

use YaSD\QBitTorrent\Exception\InvalidArgumentException;
use YaSD\QBitTorrent\Exception\NotFoundException;
use YaSD\QBitTorrent\Exception\OperationFailedException;
use YaSD\QBitTorrent\Traits\ResponseBody;
use YaSD\QBitTorrent\Traits\ResponseCode;

class TorrentRenameFolder extends Torrent
{
    use ResponseBody\DoNothing;
    use ResponseCode\Non200Codes;

    public function __construct(
        protected string $hash,
        protected string $oldPath,
        protected string $newPath
    ) {
    }

    protected function handleNon200Codes(int $code): void
    {
        if (404 === $code) {
            throw NotFoundException::forTorHash($this->hash);
        }
        if (409 === $code) {
            throw OperationFailedException::forCode409('Invalid newPath or oldPath, or newPath already in use');
        }
        if (400 === $code) {
            throw new InvalidArgumentException('Invalid_Argument: Missing newPath parameter');
        }
    }

    protected function getApiName(): string
    {
        return 'renameFolder';
    }
}
