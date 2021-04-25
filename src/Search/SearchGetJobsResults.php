<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Search;

use YaSD\QBitTorrent\Exception\InvalidArgumentException;
use YaSD\QBitTorrent\Exception\NotFoundException;
use YaSD\QBitTorrent\Traits\ResponseBody;
use YaSD\QBitTorrent\Traits\ResponseCode;

class SearchGetJobsResults extends Search
{
    use ResponseBody\ReturnJson;
    use ResponseCode\Non200Codes;

    public function __construct(
        protected int $id,
        protected int $limit = null,
        protected int $offset = null
    ) {
    }

    protected function handleNon200Codes(int $code): void
    {
        if (404 === $code) {
            throw NotFoundException::forSearchJob($this->id);
        }
        if (409 === $code) {
            throw InvalidArgumentException::forSearchResultsOffset($this->offset);
        }
    }

    protected function getApiName(): string
    {
        return 'results';
    }
}
