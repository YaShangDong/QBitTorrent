<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Torrent;

use Http\Message\MultipartStream\MultipartStreamBuilder;
use Psr\Http\Message\RequestInterface;
use YaSD\QBitTorrent\Client;
use YaSD\QBitTorrent\Exception\InvalidArgumentException;
use YaSD\QBitTorrent\Exception\OperationFailedException;
use YaSD\QBitTorrent\Traits\ResponseBody;
use YaSD\QBitTorrent\Traits\ResponseCode;

class TorrentAdd extends Torrent
{
    use ResponseBody\DoNothing;
    use ResponseCode\Non200Codes;

    /**
     * @param null|string[] $urls
     * @param null|string[] $torrents
     */
    protected function __construct(
        protected array $urls = null,
        protected array $torrents = null,
        protected string $savepath = null,
        protected string $cookie = null,
        protected string $category = null,
        protected string $tags = null,
        protected string $skip_checking = null,
        protected string $paused = null,
        protected string $root_folder = null,
        protected string $rename = null,
        protected int $upLimit = null,
        protected int $dlLimit = null,
        protected float $ratioLimit = null,
        protected int $seedingTimeLimit = null,
        protected bool $autoTMM = null,
        protected string $sequentialDownload = null,
        protected string $firstLastPiecePrio = null,
    ) {
        if (empty($urls) && empty($torrents)) {
            throw InvalidArgumentException::fromTorrentAdd();
        }
    }

    /**
     * @param string[] $links
     */
    public static function fromUrls(
        array $urls,
        string $savepath = null,
        string $cookie = null,
        string $category = null,
        string $tags = null,
        string $skip_checking = null,
        string $paused = null,
        string $root_folder = null,
        string $rename = null,
        int $upLimit = null,
        int $dlLimit = null,
        float $ratioLimit = null,
        int $seedingTimeLimit = null,
        bool $autoTMM = null,
        string $sequentialDownload = null,
        string $firstLastPiecePrio = null
    ): static {
        return new static($urls, null, $savepath, $cookie, $category, $tags, $skip_checking, $paused, $root_folder, $rename, $upLimit, $dlLimit, $ratioLimit, $seedingTimeLimit, $autoTMM, $sequentialDownload, $firstLastPiecePrio);
    }

    /**
     * @param string[] $files
     */
    public static function fromTorrents(
        array $torrents,
        string $savepath = null,
        string $cookie = null,
        string $category = null,
        string $tags = null,
        string $skip_checking = null,
        string $paused = null,
        string $root_folder = null,
        string $rename = null,
        int $upLimit = null,
        int $dlLimit = null,
        float $ratioLimit = null,
        int $seedingTimeLimit = null,
        bool $autoTMM = null,
        string $sequentialDownload = null,
        string $firstLastPiecePrio = null
    ): static {
        return new static(null, $torrents, $savepath, $cookie, $category, $tags, $skip_checking, $paused, $root_folder, $rename, $upLimit, $dlLimit, $ratioLimit, $seedingTimeLimit, $autoTMM, $sequentialDownload, $firstLastPiecePrio);
    }

    // override
    public function buildRequest(Client $client): RequestInterface
    {
        $requestBuilder = $client->getRequestBuilder();
        $requestBuilder->setMethod('POST')->setUri($this->getUri());

        $multipartBuilder = new MultipartStreamBuilder($client->getStreamFactory());

        // headers
        $requestBuilder->addCookie();
        $requestBuilder->addHeader('Content-Type', sprintf('multipart/form-data; boundary="%s"', $multipartBuilder->getBoundary()));

        // body
        foreach (get_object_vars($this) as $propName => $propValue) {
            if (null === $propValue) {
                continue;
            }
            if ('torrents' === $propName) {
                foreach ($propValue as $torFilePath) {
                    $multipartBuilder
                        ->addResource($propName, fopen($torFilePath, 'r'), ['filename' => basename($torFilePath)])
                    ;
                }
            } elseif ('urls' === $propName) {
                $multipartBuilder->addResource($propName, implode("\n", $propValue));
            } else {
                $multipartBuilder->addResource($propName, $propValue);
            }
        }
        $body = $multipartBuilder->build();

        // build
        return $requestBuilder->build($body);
    }

    protected function handleNon200Codes(int $code): void
    {
        if (415 === $code) {
            throw OperationFailedException::fromTorrentAdd415();
        }
    }

    protected function getApiName(): string
    {
        return 'add';
    }
}
