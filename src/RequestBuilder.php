<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use YaSD\QBitTorrent\Exception\UnauthorizedException;

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
        protected StreamFactoryInterface $streamFactory,
        protected Client $client
    ) {
        $this->uri = $this->uriFactory->createUri('')->withHost($host)->withPort($port);
    }

    public function setMethod(string $method): static
    {
        $this->method = $method;
        return $this;
    }

    public function setUri(string $uri): static
    {
        $this->uri = $this->uri->withPath($uri);
        return $this;
    }

    public function addCookie(): static
    {
        if (empty($this->client->getCookieSID())) {
            throw UnauthorizedException::forCookie();
        }
        return $this->addHeader('Cookie', sprintf('SID=%s', $this->client->getCookieSID()));
    }

    public function addReferer(): static
    {
        // Set Referer or Origin header to the exact same domain and port as used in the HTTP query Host header.
        return $this->addHeader('Referer', (string) $this->uri->withPath(''));
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
