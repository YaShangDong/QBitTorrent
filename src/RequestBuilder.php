<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

class RequestBuilder
{
    protected UriInterface $uri;
    protected string $method = 'POST';
    protected array $headers = [];

    public function __construct(
        string $host,
        int $port,
        protected UriFactoryInterface $uriFactory,
        protected RequestFactoryInterface $requestFactory,
        protected StreamFactoryInterface $streamFactory
    ) {
        $this->uri = $this->uriFactory->createUri('')->withHost($host)->withPort($port);
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function setMethod(string $method): static
    {
        $this->method = $method;
        return $this;
    }

    public function setUri(?string $uri): static
    {
        $this->uri = $this->uri->withPath($uri);
        return $this;
    }

    /**
     * @param string|string[] $headerValue
     */
    public function addHeader(string $headerName, string | array $headerValue): static
    {
        $this->headers[$headerName] = $headerValue;
        return $this;
    }

    public function build(StreamInterface $body): RequestInterface
    {
        $request = $this->requestFactory->createRequest($this->method, $this->uri);
        foreach ($this->headers as $headerName => $headerValue) {
            $request = $request->withHeader($headerName, $headerValue);
        }
        return $request->withBody($body);
    }
}
