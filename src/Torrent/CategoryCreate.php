<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Torrent;

use YaSD\QBitTorrent\Exception\InvalidArgumentException;
use YaSD\QBitTorrent\Traits\ResponseBody;
use YaSD\QBitTorrent\Traits\ResponseCode;

class CategoryCreate extends Torrent
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
        if (400 === $code || 409 === $code) {
            throw InvalidArgumentException::forInvalidCategory($this->category);
        }
    }

    protected function getApiName(): string
    {
        return 'createCategory';
    }
}
