<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Torrent;

use YaSD\QBitTorrent\Exception\InvalidArgumentException;
use YaSD\QBitTorrent\Exception\OperationFailedException;
use YaSD\QBitTorrent\Traits\ResponseBody;
use YaSD\QBitTorrent\Traits\ResponseCode;

class CategoryEdit extends Torrent
{
    use ResponseBody\DoNothing;
    use ResponseCode\Non200Codes;

    public function __construct(
        protected string $category,
        protected string $savePath,
    ) {
    }

    protected function handleNon200Codes(int $code): void
    {
        if (400 === $code) {
            throw InvalidArgumentException::forInvalidCategory($this->category);
        }
        if (409 === $code) {
            throw OperationFailedException::forCode409('Category editing failed');
        }
    }

    protected function getApiName(): string
    {
        return 'editCategory';
    }
}
