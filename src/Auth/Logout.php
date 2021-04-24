<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Auth;

use Psr\Http\Message\ResponseInterface;
use YaSD\QBitTorrent\Client;
use YaSD\QBitTorrent\Traits\ResponseBody;
use YaSD\QBitTorrent\Traits\ResponseCode;

class Logout extends Auth
{
    use ResponseBody\DoNothing;
    use ResponseCode\Code200;

    // override
    public function handleResponse(ResponseInterface $response, Client $client)
    {
        $this->handleResponseCode($response->getStatusCode());

        // clear cookie
        $client->setCookieSID(null);

        return $this->handleResponseBody((string) $response->getBody());
    }

    protected function getApiName(): string
    {
        return 'logout';
    }
}
