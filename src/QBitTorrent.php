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

    /**
     * Search: start search job.
     *
     * @param string $pattern  Pattern to search for (e.g. "Ubuntu 18.04")
     * @param string $plugins  Plugins to use for searching (e.g. "legittorrents"). Supports multiple plugins separated by |. Also supports all and enabled
     * @param string $category Categories to limit your search to (e.g. "legittorrents"). Available categories depend on the specified plugins. Also supports all
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     * @throws OperationFailedException    user has reached the limit of max running searches (currently set to 5)
     *
     * @return array JSON
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#start-search
     */
    public function searchStartJob(string $pattern, string $plugins, string $category): array
    {
        $api = new Search\SearchStartJob($pattern, $plugins, $category);
        return $this->client->execute($api);
    }

    /**
     * Search: stop search job.
     *
     * @param int $id ID of the search job
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     * @throws NotFoundException           search job was not found
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#stop-search
     */
    public function searchStopJob(int $id): static
    {
        $api = new Search\SearchStopJob($id);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Search: get search job status.
     *
     * @param null|int $id ID of the search job. If not specified, all search jobs are returned
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     * @throws NotFoundException           search job was not found
     *
     * @return array JSON
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#get-search-status
     */
    public function searchGetJobsStatus(int $id = null): array
    {
        $api = new Search\SearchGetJobsStatus($id);
        return $this->client->execute($api);
    }

    /**
     * Search: get search job results.
     *
     * @param int $id     ID of the search job
     * @param int $limit  max number of results to return. 0 or negative means no limit
     * @param int $offset result to start at. A negative number means count backwards (e.g. -2 returns the 2 most recent results)
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     * @throws NotFoundException           search job was not found
     * @throws InvalidArgumentException    offset is too large, or too small (e.g. absolute value of negative number is greater than # results)
     *
     * @return array JSON
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#get-search-results
     */
    public function searchGetJobsResults(int $id, int $limit = null, int $offset = null): array
    {
        $api = new Search\SearchGetJobsResults($id, $limit, $offset);
        return $this->client->execute($api);
    }

    /**
     * Search: delete search job.
     *
     * @param int $id ID of the search job
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     * @throws NotFoundException           search job was not found
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#delete-search
     */
    public function searchDeleteJob(int $id): static
    {
        $api = new Search\SearchDeleteJob($id);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Search: get all search plugins.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return array JSON
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#get-search-plugins
     */
    public function searchGetAllPlugins(): array
    {
        $api = new Search\SearchGetAllPlugins();
        return $this->client->execute($api);
    }

    /**
     * Search: install search plugin.
     *
     * @param string $sources Url or file path of the plugin to install (e.g. "https://raw.githubusercontent.com/qbittorrent/search-plugins/master/nova3/engines/legittorrents.py"). Supports multiple sources separated by |
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#install-search-plugin
     */
    public function searchInstallPlugin(string $sources): static
    {
        $api = new Search\SearchInstallPlugin($sources);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Search: uninstall search plugin.
     *
     * @param string $names Name of the plugin to uninstall (e.g. "legittorrents"). Supports multiple names separated by |
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#uninstall-search-plugin
     */
    public function searchUninstallPlugin(string $names): static
    {
        $api = new Search\SearchUninstallPlugin($names);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Search: enable search plugin.
     *
     * @param string $names  Name of the plugin to enable/disable (e.g. "legittorrents"). Supports multiple names separated by |
     * @param bool   $enable Whether the plugins should be enabled
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#enable-search-plugin
     */
    public function searchEnablePlugin(string $names, bool $enable): static
    {
        $api = new Search\SearchEnablePlugin($names, $enable);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Search: update search plugins.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#update-search-plugins
     */
    public function searchUpdatePlugins(): static
    {
        $api = new Search\SearchUpdatePlugins();
        $this->client->execute($api);
        return $this;
    }
}
