<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use YaSD\QBitTorrent\Exception\InvalidArgumentException;
use YaSD\QBitTorrent\Exception\LoginFailedException;
use YaSD\QBitTorrent\Exception\TooManyFailedLoginException;
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
     * @throws TooManyFailedLoginException User's IP is banned for too many failed login attempts
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

    /**
     * Get WebAPI version.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return string WebAPI version, e.g. `2.0`
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#get-api-version
     */
    public function apiGetVersion(): string
    {
        $api = new App\ApiGetVersion();
        return $this->client->execute($api);
    }

    /**
     * Get app version.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return string application version, e.g. `v4.1.3`
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#get-application-version
     */
    public function appGetVersion(): string
    {
        $api = new App\AppGetVersion();
        return $this->client->execute($api);
    }

    /**
     * Get build info.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return array JSON
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#get-build-info
     */
    public function appGetBuildInfo(): array
    {
        $api = new App\AppGetBuildInfo();
        return $this->client->execute($api);
    }

    /**
     * Shutdown application.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#shutdown-application
     */
    public function appShutdown(): static
    {
        $api = new App\AppShutdown();
        $this->client->execute($api);
        return $this;
    }

    /**
     * Get default save path.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return string default save path, e.g. `C:/Users/Dayman/Downloads`
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#get-default-save-path
     */
    public function appGetDefaultSavePath(): string
    {
        $api = new App\AppGetDefaultSavePath();
        return $this->client->execute($api);
    }

    /**
     * Get application preferences.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return array JSON with key-value pairs representing the application's settings
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#get-application-preferences
     */
    public function appGetSettings(): array
    {
        $api = new App\AppGetSettings();
        return $this->client->execute($api);
    }

    /**
     * Set application preferences.
     *
     * @param string $json A json object with key-value pairs of the settings you want to change and their new values
     *
     * @throws InvalidArgumentException    invalid argument: invalid json
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#set-application-preferences
     */
    public function appSetSettings(string $json): static
    {
        if (!verifyJson($json)) {
            throw InvalidArgumentException::forInvalidJson($json);
        }
        $api = new App\AppSetSettings($json);
        $this->client->execute($api);
        return $this;
    }
}
