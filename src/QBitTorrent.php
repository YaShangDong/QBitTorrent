<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use YaSD\QBitTorrent\Exception\LoginFailedException;
use YaSD\QBitTorrent\Exception\tooManyFailedLoginException;
use YaSD\QBitTorrent\Exception\UnauthorizedException;
use YaSD\QBitTorrent\Exception\UnexpectedResponseException;

class qBitTorrent
{
    protected Client $client;

    public function __construct(
        string $host = 'localhost',
        int $port = 8080,
        ClientInterface $httpClient = null,
        UriFactoryInterface $uriFactory = null,
        RequestFactoryInterface $requestFactory = null,
        StreamFactoryInterface $streamFactory = null,
    ) {
        $this->client = new Client($host, $port, $httpClient, $uriFactory, $requestFactory, $streamFactory);
    }

    /**
     * Login.
     *
     * @param string $username Username used to access the WebUI
     * @param string $password Password used to access the WebUI
     *
     * @throws LoginFailedException        login failed
     * @throws tooManyFailedLoginException User's IP is banned for too many failed login attempts
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#login
     */
    public function login(string $username = 'admin', string $password = 'adminadmin'): static
    {
        $api = new Auth\Login($username, $password);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Logout.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#logout
     */
    public function logout(): static
    {
        $api = new Auth\Logout();
        $this->client->execute($api);
        return $this;
    }
}
