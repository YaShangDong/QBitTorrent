<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Auth;

use Psr\Http\Message\ResponseInterface;
use YaSD\QBitTorrent\Client;
use YaSD\QBitTorrent\Exception\LoginFailedException;
use YaSD\QBitTorrent\Exception\TooManyFailedLoginException;
use YaSD\QBitTorrent\RequestBuilder;
use YaSD\QBitTorrent\Traits\ResponseBody;
use YaSD\QBitTorrent\Traits\ResponseCode;

class Login extends Auth
{
    use ResponseBody\DoNothing;
    use ResponseCode\Non200Codes;

    public function __construct(
        protected string $username,
        protected string $password,
    ) {
    }

    // override
    public function handleResponse(ResponseInterface $response, Client $client)
    {
        $this->handleResponseCode($response->getStatusCode());

        // Set-Cookie
        $client->setCookieSID(null);
        $cookies = $response->getHeader('Set-Cookie');
        foreach ($cookies as $cookie) {
            if (preg_match('/SID=([^;]+)/i', $cookie, $matches)) {
                $client->setCookieSID($matches[1]);
            }
        }
        if (empty($client->getCookieSID())) {
            throw LoginFailedException::forCookie();
        }

        return $this->handleResponseBody((string) $response->getBody());
    }

    // override
    protected function setRequestHeaders(RequestBuilder $requestBuilder): RequestBuilder
    {
        // add Referer, no Cookie
        return $requestBuilder->addReferer();
    }

    protected function handleNon200Codes(int $code): void
    {
        if (403 === $code) {
            throw TooManyFailedLoginException::fromLogin();
        }
    }

    protected function getApiName(): string
    {
        return 'login';
    }
}
