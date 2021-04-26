<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

abstract class Api
{
    public function buildRequest(Client $client): RequestInterface
    {
        $builder = $client->getRequestBuilder();
        $builder->setMethod('POST')->setUri($this->getUri());
        $builder->addHeader('Content-Type', 'application/x-www-form-urlencoded');
        $builder = $this->setRequestHeaders($builder);
        $body = $this->buildRequestBody($client->getStreamFactory());
        return $builder->build($body);
    }

    public function handleResponse(ResponseInterface $response, Client $client)
    {
        $this->handleResponseCode($response->getStatusCode());
        return $this->handleResponseBody((string) $response->getBody());
    }

    protected function getUri(): string
    {
        return sprintf(
            '/api/v2/%s/%s',
            $this->getApiGroup(),
            $this->getApiName()
        );
    }

    // default: auth
    protected function setRequestHeaders(RequestBuilder $requestBuilder): RequestBuilder
    {
        return $requestBuilder->addCookie();
    }

    protected function buildRequestBody(StreamFactoryInterface $streamFactory): StreamInterface
    {
        $params = array_filter(get_object_vars($this), function ($propValue) {
            return null !== $propValue;
        });
        return $streamFactory->createStream(http_build_query($params));
    }

    abstract protected function getApiGroup(): string;

    abstract protected function getApiName(): string;

    abstract protected function handleResponseCode(int $code): void;

    abstract protected function handleResponseBody(string $body);
}
