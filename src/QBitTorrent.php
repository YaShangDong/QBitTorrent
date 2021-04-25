<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use YaSD\QBitTorrent\Exception\InvalidArgumentException;
use YaSD\QBitTorrent\Exception\LoginFailedException;
use YaSD\QBitTorrent\Exception\NotFoundException;
use YaSD\QBitTorrent\Exception\OperationFailedException;
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

    /**
     * Get logs.
     *
     * @param bool $normal        Include normal messages
     * @param bool $info          Include info messages
     * @param bool $warning       Include warning messages
     * @param bool $critical      Include critical messages
     * @param int  $last_known_id Exclude messages with "message id" <= last_known_id
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return array JSON array in which each element is an entry of the log
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#get-log
     */
    public function logGetLogs(
        bool $normal = true,
        bool $info = true,
        bool $warning = true,
        bool $critical = true,
        int $last_known_id = -1
    ): array {
        $api = new Log\LogGetLogs($normal, $info, $warning, $critical, $last_known_id);
        return $this->client->execute($api);
    }

    /**
     * Get peer log.
     *
     * @param int $last_known_id Exclude messages with "message id" <= last_known_id
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return array JSON
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#get-peer-log
     */
    public function logGetPeers(int $last_known_id = -1): array
    {
        $api = new Log\LogGetPeers($last_known_id);
        return $this->client->execute($api);
    }

    /**
     * Sync: Get main data.
     *
     * @param int $rid Response ID. If not provided, `rid=0` will be assumed. If the given `rid` is different from the one of last server reply, `full_update` will be `true`
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return array JSON
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#get-main-data
     */
    public function syncGetData(int $rid = 0): array
    {
        $api = new Sync\SyncGetData($rid);
        return $this->client->execute($api);
    }

    /**
     * Sync: Get torrent peers data.
     *
     * @param string $hash Torrent hash
     * @param int    $rid  Response ID. If not provided, `rid=0` will be assumed. If the given `rid` is different from the one of last server reply, `full_update` will be `true`
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     * @throws NotFoundException           torrent hash was not found
     *
     * @return array JSON
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#get-torrent-peers-data
     */
    public function syncGetPeers(string $hash, int $rid = 0): array
    {
        $api = new Sync\SyncGetPeers($hash, $rid);
        return $this->client->execute($api);
    }

    /**
     * RSS: Get all items.
     *
     * @param bool $withData True if you need current feed articles
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return array JSON
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#get-all-items
     */
    public function rssGetAllItems(bool $withData = false): array
    {
        $api = new Rss\RssGetAllItems($withData);
        return $this->client->execute($api);
    }

    /**
     * RSS: Add feed.
     *
     * @param string      $url  URL of RSS feed (e.g. "http://thepiratebay.org/rss//top100/200")
     * @param null|string $path Full path of added folder (e.g. "The Pirate Bay\Top100\Video")
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     * @throws OperationFailedException    failure to add feed
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#add-feed
     */
    public function rssAddFeed(string $url, string $path = null): static
    {
        $api = new Rss\RssAddFeed($url, $path);
        $this->client->execute($api);
        return $this;
    }

    /**
     * RSS: Add folder.
     *
     * @param string $path full path of added folder (e.g. "The Pirate Bay\Top100")
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     * @throws OperationFailedException    failure to add folder
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#add-folder
     */
    public function rssAddFolder(string $path): static
    {
        $api = new Rss\RssAddFolder($path);
        $this->client->execute($api);
        return $this;
    }

    /**
     * RSS: Mark article/feed as read.
     *
     * If $articleId is provided only the article is marked as read otherwise the whole feed is going to be marked as read.
     *
     * @param string      $itemPath  Current full path of item (e.g. "The Pirate Bay\Top100")
     * @param null|string $articleId ID of article
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#mark-as-read
     */
    public function rssMarkItemAsRead(string $itemPath, string $articleId = null): static
    {
        $api = new Rss\RssMarkItemAsRead($itemPath, $articleId);
        $this->client->execute($api);
        return $this;
    }

    /**
     * RSS: Moves/renames folder or feed.
     *
     * @param string $itemPath Current full path of item (e.g. "The Pirate Bay\Top100")
     * @param string $destPath New full path of item (e.g. "The Pirate Bay")
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     * @throws OperationFailedException    failure to move item
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#move-item
     */
    public function rssMoveItem(string $itemPath, string $destPath): static
    {
        $api = new Rss\RssMoveItem($itemPath, $destPath);
        $this->client->execute($api);
        return $this;
    }

    /**
     * RSS: Refresh folder or feed.
     *
     * @param string $itemPath Current full path of item (e.g. "The Pirate Bay\Top100")
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#refresh-item
     */
    public function rssRefreshItem(string $itemPath): static
    {
        $api = new Rss\RssRefreshItem($itemPath);
        $this->client->execute($api);
        return $this;
    }

    /**
     * RSS: Removes folder or feed.
     *
     * @param string $path Full path of removed item (e.g. "The Pirate Bay\Top100")
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     * @throws OperationFailedException    failure to remove item
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#remove-item
     */
    public function rssRemoveItem(string $path): static
    {
        $api = new Rss\RssRemoveItem($path);
        $this->client->execute($api);
        return $this;
    }

    /**
     * RSS: Get all auto-downloading rules.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return array all auto-downloading rules in JSON format
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#get-all-auto-downloading-rules
     */
    public function rssGetAllRules(): array
    {
        $api = new Rss\RssGetAllRules();
        return $this->client->execute($api);
    }

    /**
     * RSS: Set auto-downloading rule.
     *
     * @todo validate rule json syntax
     *
     * @param string $ruleName Rule name (e.g. "Punisher")
     * @param string $ruleDef  JSON encoded rule definition
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#set-auto-downloading-rule
     */
    public function rssSetRule(string $ruleName, string $ruleDef): static
    {
        $api = new Rss\RssSetRule($ruleName, $ruleDef);
        $this->client->execute($api);
        return $this;
    }

    /**
     * RSS: Remove auto-downloading rule.
     *
     * @param string $ruleName Rule name (e.g. "Punisher")
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#remove-auto-downloading-rule
     */
    public function rssRemoveRule(string $ruleName): static
    {
        $api = new Rss\RssRemoveRule($ruleName);
        $this->client->execute($api);
        return $this;
    }

    /**
     * RSS: Rename auto-downloading rule.
     *
     * @param string $ruleName    Rule name (e.g. "Punisher")
     * @param string $newRuleName New rule name (e.g. "The Punisher")
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#rename-auto-downloading-rule
     */
    public function rssRenameRule(string $ruleName, string $newRuleName): static
    {
        $api = new Rss\RssRenameRule($ruleName, $newRuleName);
        $this->client->execute($api);
        return $this;
    }

    /**
     * RSS: Get all articles matching a rule.
     *
     * @param string $ruleName Rule name (e.g. "Linux")
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return array all articles that match a rule by feed name in JSON format
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#get-all-articles-matching-a-rule
     */
    public function rssMatchRule(string $ruleName): array
    {
        $api = new Rss\RssMatchRule($ruleName);
        return $this->client->execute($api);
    }
}
