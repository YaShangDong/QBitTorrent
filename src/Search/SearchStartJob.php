<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Search;

use YaSD\QBitTorrent\Exception\OperationFailedException;
use YaSD\QBitTorrent\Traits\ResponseBody;
use YaSD\QBitTorrent\Traits\ResponseCode;

class SearchStartJob extends Search
{
    use ResponseBody\ReturnJson;
    use ResponseCode\Non200Codes;

    public function __construct(
        protected string $pattern,
        protected string $plugins,
        protected string $category,
    ) {
    }

    protected function handleNon200Codes(int $code): void
    {
        if (409 === $code) {
            throw OperationFailedException::fromSearchStartJob409();
        }
    }

    protected function getApiName(): string
    {
        return 'start';
    }
}
