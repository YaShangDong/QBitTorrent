<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Torrent;

use YaSD\QBitTorrent\Traits\ResponseBody;
use YaSD\QBitTorrent\Traits\ResponseCode;

class TorrentList extends Torrent
{
    use ResponseBody\ReturnJson;
    use ResponseCode\Code200;

    public function __construct(
        protected ?string $filter = null,
        protected ?string $category = null,
        protected ?string $sort = null,
        protected ?bool $reverse = null,
        protected ?int $limit = null,
        protected ?int $offset = null,
        protected ?string $hashes = null,
    ) {
    }

    protected function getApiName(): string
    {
        return 'info';
    }
}
