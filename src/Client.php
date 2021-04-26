<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

class Client
{
    protected ?string $cookieSID = null;

    public function __construct(
        protected string $host,
        protected int $port,
        protected ?ClientInterface $httpClient = null,
        protected ?UriFactoryInterface $uriFactory = null,
        protected ?RequestFactoryInterface $requestFactory = null,
        protected ?StreamFactoryInterface $streamFactory = null,
    ) {
        $this->httpClient = $httpClient ?: Psr18ClientDiscovery::find();
        $this->uriFactory = $uriFactory ?: Psr17FactoryDiscovery::findUriFactory();
        $this->requestFactory = $requestFactory ?: Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = $streamFactory ?: Psr17FactoryDiscovery::findStreamFactory();
    }

    public function getCookieSID(): ?string
    {
        return $this->cookieSID;
    }

    public function setCookieSID(?string $cookieSID): static
    {
        $this->cookieSID = $cookieSID;
        return $this;
    }

    public function getStreamFactory(): StreamFactoryInterface
    {
        return $this->streamFactory;
    }

    public function getRequestBuilder(): RequestBuilder
    {
        return new RequestBuilder($this->host, $this->port, $this->uriFactory, $this->requestFactory, $this->streamFactory, $this);
    }

    public function execute(Api $api)
    {
        $request = $api->buildRequest($this);
        $response = $this->httpClient->sendRequest($request);
        return $api->handleResponse($response, $this);
    }
}
