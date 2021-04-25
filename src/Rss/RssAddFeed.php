<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Rss;

use YaSD\QBitTorrent\Exception\OperationFailedException;
use YaSD\QBitTorrent\Traits\ResponseBody;
use YaSD\QBitTorrent\Traits\ResponseCode;

class RssAddFeed extends Rss
{
    use ResponseBody\DoNothing;
    use ResponseCode\Non200Codes;

    public function __construct(
        protected string $url,
        protected string $path = null,
    ) {
    }

    protected function handleNon200Codes(int $code): void
    {
        if (409 === $code) {
            throw OperationFailedException::fromRssAddFeed409();
        }
    }

    protected function getApiName(): string
    {
        return 'addFeed';
    }
}
