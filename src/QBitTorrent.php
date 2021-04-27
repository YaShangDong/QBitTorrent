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

class QBitTorrent
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

    /**
     * Transfer: Get global transfer info.
     *
     * This method returns info you usually see in qBt status bar
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return array JSON
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#get-global-transfer-info
     */
    public function transferGetInfo(): array
    {
        $api = new Transfer\TransferGetInfo();
        return $this->client->execute($api);
    }

    /**
     * Transfer: Get alternative speed limits state.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return int 1 if alternative speed limits are enabled, 0 otherwise
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#get-alternative-speed-limits-state
     */
    public function transferGetMode(): int
    {
        $api = new Transfer\TransferGetMode();
        return $this->client->execute($api);
    }

    /**
     * Transfer: Toggle alternative speed limits.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#toggle-alternative-speed-limits
     */
    public function transferToggleMode(): static
    {
        $api = new Transfer\TransferToggleMode();
        $this->client->execute($api);
        return $this;
    }

    /**
     * Transfer: Get global download limit.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return int the value of current global download speed limit in bytes/second; this value will be zero if no limit is applied
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#get-global-download-limit
     */
    public function transferGetDownloadLimit(): int
    {
        $api = new Transfer\TransferGetDownloadLimit();
        return $this->client->execute($api);
    }

    /**
     * Transfer: Set global download limit.
     *
     * @param int $limit The global download speed limit to set in bytes/second
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#set-global-download-limit
     */
    public function transferSetDownloadLimit(int $limit): static
    {
        $api = new Transfer\TransferSetDownloadLimit($limit);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Transfer: Get global upload limit.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return int current global upload speed limit in bytes/second; this value will be zero if no limit is applied
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#get-global-upload-limit
     */
    public function transferGetUploadLimit(): int
    {
        $api = new Transfer\TransferGetUploadLimit();
        return $this->client->execute($api);
    }

    /**
     * Transfer: Set global upload limit.
     *
     * @param int $limit The global upload speed limit to set in bytes/second
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#set-global-upload-limit
     */
    public function transferSetUploadLimit(int $limit): static
    {
        $api = new Transfer\TransferSetUploadLimit($limit);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Transfer: Ban peers.
     *
     * @param string|string[] $peers The peers to ban, Each peer is a colon-separated host:port
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#ban-peers
     */
    public function transferBanPeers(string | array $peers): static
    {
        $peers = \is_array($peers) ? implode('|', $peers) : $peers;
        $api = new Transfer\TransferBanPeers($peers);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Category: Add new category.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     * @throws InvalidArgumentException    Category name is invalid
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#add-new-category
     */
    public function categoryCreate(string $category, string $savePath): static
    {
        $api = new Torrent\CategoryCreate($category, $savePath);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Category: Edit category.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     * @throws InvalidArgumentException    Category name is empty
     * @throws OperationFailedException    Category editing failed
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#edit-category
     */
    public function categoryEdit(string $category, string $savePath): static
    {
        $api = new Torrent\CategoryEdit($category, $savePath);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Category: Remove categories.
     *
     * @param string $categories categories can contain multiple cateogies separated by `\n` (`%0A` urlencoded)
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#remove-categories
     */
    public function categoryRemove(string $categories): static
    {
        $api = new Torrent\CategoryRemove($categories);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Category: Get all categories.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return array Returns all categories in JSON format
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#get-all-categories
     */
    public function categoryList(): array
    {
        $api = new Torrent\CategoryList();
        return $this->client->execute($api);
    }

    /**
     * Tag: Get all tags.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return array Returns all tags in JSON format
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#get-all-tags
     */
    public function tagList(): array
    {
        $api = new Torrent\TagList();
        return $this->client->execute($api);
    }

    /**
     * Tag: Create tags.
     *
     * @param string|string[] $tagNames
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#create-tags
     */
    public function tagCreate(string | array $tagNames): static
    {
        $tags = \is_array($tagNames) ? implode(',', $tagNames) : $tagNames;
        $api = new Torrent\TagCreate($tags);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Tag: Delete tags.
     *
     * @param string|string[] $tagNames
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#delete-tags
     */
    public function tagDelete(string | array $tagNames): static
    {
        $tags = \is_array($tagNames) ? implode(',', $tagNames) : $tagNames;
        $api = new Torrent\TagDelete($tags);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Torrent: Get torrent list.
     *
     * @param null|string $filter   Filter torrent list by state. Allowed state filters: `all`, `downloading`, `completed`, `paused`, `active`, `inactive`, `resumed`, `stalled`, `stalled_uploading`, `stalled_downloading`
     * @param null|string $category Get torrents with the given category (empty string means "without category"; no "category" parameter means "any category". Remember to URL-encode the category name. For example, `My category` becomes `My%20category`
     * @param null|string $sort     Sort torrents by given key. They can be sorted using any field of the response's JSON array (which are documented below) as the sort key.
     * @param null|bool   $reverse  Enable reverse sorting. Defaults to `false`
     * @param null|int    $limit    Limit the number of torrents returned
     * @param null|int    $offset   Set offset (if less than 0, offset from end)
     * @param null|string $hashes   Filter by hashes. Can contain multiple hashes separated by `|`
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return array JSON
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#get-torrent-list
     */
    public function torrentList(
        string $filter = null,
        string $category = null,
        string $sort = null,
        bool $reverse = null,
        int $limit = null,
        int $offset = null,
        string $hashes = null,
    ): array {
        if ($category) {
            // Remember to URL-encode the category name. For example, `My category` becomes `My%20category`
            $category = urlencode(urldecode($category));
        }
        $api = new Torrent\TorrentList($filter, $category, $sort, $reverse, $limit, $offset, $hashes);
        return $this->client->execute($api);
    }

    /**
     * Torrent: Get torrent generic properties.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     * @throws NotFoundException           Torrent hash was not found
     *
     * @return array JSON
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#get-torrent-generic-properties
     */
    public function torrentGetProperties(string $hash): array
    {
        $api = new Torrent\TorrentGetProperties($hash);
        return $this->client->execute($api);
    }

    /**
     * Torrent: Get torrent trackers.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     * @throws NotFoundException           Torrent hash was not found
     *
     * @return array JSON array, where each element contains info about one tracker
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#get-torrent-trackers
     */
    public function torrentGetTrackers(string $hash): array
    {
        $api = new Torrent\TorrentGetTrackers($hash);
        return $this->client->execute($api);
    }

    /**
     * Torrent: Get torrent web seeds.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     * @throws NotFoundException           Torrent hash was not found
     *
     * @return array a JSON array, where each element is information about one webseed
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#get-torrent-web-seeds
     */
    public function torrentGetWebSeeds(string $hash): array
    {
        $api = new Torrent\TorrentGetWebSeeds($hash);
        return $this->client->execute($api);
    }

    /**
     * Torrent: Get torrent contents.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     * @throws NotFoundException           Torrent hash was not found
     *
     * @return array JSON array, where each element contains info about one file
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#get-torrent-contents
     */
    public function torrentGetContents(string $hash): array
    {
        $api = new Torrent\TorrentGetContents($hash);
        return $this->client->execute($api);
    }

    /**
     * Torrent: Get torrent pieces' states.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     * @throws NotFoundException           Torrent hash was not found
     *
     * @return array an array of states (integers) of all pieces (in order) of a specific torrent
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#get-torrent-pieces-states
     */
    public function torrentGetPiecesStates(string $hash): array
    {
        $api = new Torrent\TorrentGetPiecesStates($hash);
        return $this->client->execute($api);
    }

    /**
     * Torrent: Get torrent pieces' hashes.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     * @throws NotFoundException           Torrent hash was not found
     *
     * @return array an array of hashes (strings) of all pieces (in order) of a specific torrent
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#get-torrent-pieces-hashes
     */
    public function torrentGetPiecesHashes(string $hash): array
    {
        $api = new Torrent\TorrentGetPiecesHashes($hash);
        return $this->client->execute($api);
    }

    /**
     * Torrent: Pause torrents.
     *
     * @param string $hashes The hashes of the torrents you want to pause. hashes can contain multiple hashes separated by `|`, to pause multiple torrents, or set to `all`, to pause all torrents.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#pause-torrents
     */
    public function torrentPause(string $hashes): static
    {
        $api = new Torrent\TorrentPause($hashes);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Torrent: Resume torrents.
     *
     * @param string $hashes The hashes of the torrents you want to resume. hashes can contain multiple hashes separated by `|`, to resume multiple torrents, or set to `all`, to resume all torrents.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#resume-torrents
     */
    public function torrentResume(string $hashes): static
    {
        $api = new Torrent\TorrentResume($hashes);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Torrent: Delete torrents.
     *
     * @param string $hashes      The hashes of the torrents you want to delete. hashes can contain multiple hashes separated by `|`, to delete multiple torrents, or set to `all`, to delete all torrents.
     * @param bool   $deleteFiles if set to `true`, the downloaded data will also be deleted, otherwise has no effect
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#delete-torrents
     */
    public function torrentDelete(string $hashes, bool $deleteFiles = false): static
    {
        $api = new Torrent\TorrentDelete($hashes, $deleteFiles);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Torrent: Recheck torrents.
     *
     * @param string $hashes The hashes of the torrents you want to recheck. hashes can contain multiple hashes separated by `|`, to recheck multiple torrents, or set to `all`, to recheck all torrents.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#recheck-torrents
     */
    public function torrentRecheck(string $hashes): static
    {
        $api = new Torrent\TorrentRecheck($hashes);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Torrent: Reannounce torrents.
     *
     * @param string $hashes The hashes of the torrents you want to reannounce. hashes can contain multiple hashes separated by `|`, to reannounce multiple torrents, or set to `all`, to reannounce all torrents.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#reannounce-torrents
     */
    public function torrentReannounce(string $hashes): static
    {
        $api = new Torrent\TorrentReannounce($hashes);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Torrent: Add trackers to torrent.
     *
     * @param string|string[] $urls tracker urls
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     * @throws NotFoundException           Torrent hash was not found
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#add-trackers-to-torrent
     */
    public function torrentAddTrackers(string $hash, string | array $urls): static
    {
        $urls = \is_array($urls) ? implode('%0A', $urls) : $urls;
        $api = new Torrent\TorrentAddTrackers($hash, $urls);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Torrent: Edit trackers.
     *
     * @param string $hash    The hash of the torrent
     * @param string $origUrl The tracker URL you want to edit
     * @param string $newUrl  The new URL to replace the origUrl
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     * @throws NotFoundException           Torrent hash was not found
     * @throws InvalidArgumentException    newUrl is not a valid URL
     * @throws OperationFailedException    newUrl already exists for the torrent
     * @throws OperationFailedException    origUrl was not found
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#edit-trackers
     */
    public function torrentEditTracker(string $hash, string $origUrl, string $newUrl): static
    {
        $api = new Torrent\TorrentEditTracker($hash, $origUrl, $newUrl);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Torrent: Remove trackers.
     *
     * @param string $hash The hash of the torrent
     * @param string $urls URLs to remove, separated by `|`
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     * @throws NotFoundException           Torrent hash was not found
     * @throws OperationFailedException    All urls were not found
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#remove-trackers
     */
    public function torrentRemoveTrackers(string $hash, string $urls): static
    {
        $api = new Torrent\TorrentRemoveTrackers($hash, $urls);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Torrent: Add peers.
     *
     * @param string $hashes The hash of the torrent, or multiple hashes separated by a pipe `|`
     * @param string $peers  The peer to add, or multiple peers separated by a pipe `|`. Each peer is a colon-separated `host:port`
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     * @throws InvalidArgumentException    None of the supplied peers are valid
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#add-peers
     */
    public function torrentAddPeers(string $hashes, string $peers): static
    {
        $api = new Torrent\TorrentAddPeers($hashes, $peers);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Torrent: Increase torrent priority.
     *
     * @param string $hashes The hashes of the torrents you want to increase the priority of. hashes can contain multiple hashes separated by `|`, to increase the priority of multiple torrents, or set to `all`, to increase the priority of all torrents.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     * @throws OperationFailedException    Torrent queueing is not enabled
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#increase-torrent-priority
     */
    public function torrentIncreasePriority(string $hashes): static
    {
        $api = new Torrent\TorrentIncreasePriority($hashes);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Torrent: Decrease torrent priority.
     *
     * @param string $hashes The hashes of the torrents you want to decrease the priority of. hashes can contain multiple hashes separated by `|`, to decrease the priority of multiple torrents, or set to `all`, to decrease the priority of all torrents.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     * @throws OperationFailedException    Torrent queueing is not enabled
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#decrease-torrent-priority
     */
    public function torrentDecreasePriority(string $hashes): static
    {
        $api = new Torrent\TorrentDecreasePriority($hashes);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Torrent: Maximal torrent priority.
     *
     * @param string $hashes The hashes of the torrents you want to set to the maximum priority. hashes can contain multiple hashes separated by `|`, to set multiple torrents to the maximum priority, or set to `all`, to set all torrents to the maximum priority.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     * @throws OperationFailedException    Torrent queueing is not enabled
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#maximal-torrent-priority
     */
    public function torrentMaximalPriority(string $hashes): static
    {
        $api = new Torrent\TorrentMaximalPriority($hashes);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Torrent: Minimal torrent priority.
     *
     * @param string $hashes The hashes of the torrents you want to set to the minimum priority. hashes can contain multiple hashes separated by |, to set multiple torrents to the minimum priority, or set to all, to set all torrents to the minimum priority.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     * @throws OperationFailedException    Torrent queueing is not enabled
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#minimal-torrent-priority
     */
    public function torrentMinimalPriority(string $hashes): static
    {
        $api = new Torrent\TorrentMinimalPriority($hashes);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Torrent: Set file priority.
     *
     * @param string $hash     The hash of the torrent
     * @param string $id       File ids, separated by `|`. `id` values coresspond to contents returned by torrent contents API, e.g. `id=0` for first file, `id=1` for second file, etc.
     * @param int    $priority File priority to set. Please consult the torrent contents API for possible `priority` values.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     * @throws NotFoundException           Torrent hash was not found
     * @throws OperationFailedException    Torrent metadata hasn't downloaded yet
     * @throws OperationFailedException    At least one file id was not found
     * @throws InvalidArgumentException    Priority is invalid
     * @throws InvalidArgumentException    At least one file id is not a valid integer
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#set-file-priority
     */
    public function torrentSetFilePrio(string $hash, string $id, int $priority): static
    {
        $api = new Torrent\TorrentSetFilePriority($hash, $id, $priority);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Torrent: Get torrent download limit.
     *
     * @param string $hashes hashes can contain multiple hashes separated by `|` or set to `all`
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return array JSON
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#get-torrent-download-limit
     */
    public function torrentGetDownloadLimit(string $hashes): array
    {
        $api = new Torrent\TorrentGetDownloadLimit($hashes);
        return $this->client->execute($api);
    }

    /**
     * Torrent: Set torrent download limit.
     *
     * @param string $hashes hashes can contain multiple hashes separated by `|` or set to `all`
     * @param int    $limit  limit is the download speed limit in bytes per second you want to set
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#set-torrent-download-limit
     */
    public function torrentSetDownloadLimit(string $hashes, int $limit): static
    {
        $api = new Torrent\TorrentSetDownloadLimit($hashes, $limit);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Torrent: Set torrent share limit.
     *
     * @param string $hashes           hashes can contain multiple hashes separated by `|` or set to `all`
     * @param float  $ratioLimit       ratioLimit is the max ratio the torrent should be seeded until. `-2` means the global limit should be used, `-1` means no limit.
     * @param int    $seedingTimeLimit seedingTimeLimit is the max amount of time the torrent should be seeded. `-2` means the global limit should be used, `-1` means no limit.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#set-torrent-share-limit
     */
    public function torrentSetShareLimit(string $hashes, float $ratioLimit, int $seedingTimeLimit): static
    {
        $api = new Torrent\TorrentSetShareLimit($hashes, $ratioLimit, $seedingTimeLimit);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Torrent: Get torrent upload limit.
     *
     * @param string $hashes hashes can contain multiple hashes separated by `|` or set to `all`
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#get-torrent-upload-limit
     */
    public function torrentGetUploadLimit(string $hashes): array
    {
        $api = new Torrent\TorrentGetUploadLimit($hashes);
        return $this->client->execute($api);
    }

    /**
     * Torrent: Set torrent upload limit.
     *
     * @param string $hashes hashes can contain multiple hashes separated by `|` or set to `all`
     * @param int    $limit  limit is the upload speed limit in bytes per second you want to set
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#set-torrent-upload-limit
     */
    public function torrentSetUploadLimit(string $hashes, int $limit): static
    {
        $api = new Torrent\TorrentSetUploadLimit($hashes, $limit);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Torrent: Set torrent location.
     *
     * @param string $hashes   hashes can contain multiple hashes separated by `|` or set to `all`
     * @param string $location location is the location to download the torrent to. If the location doesn't exist, the torrent's location is unchanged.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     * @throws InvalidArgumentException    Save path is empty
     * @throws OperationFailedException    Unable to create save path directory
     * @throws OperationFailedException    User does not have write access to directory
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#set-torrent-location
     */
    public function torrentSetLocation(string $hashes, string $location): static
    {
        $api = new Torrent\TorrentSetLocation($hashes, $location);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Torrent: Set torrent name.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     * @throws NotFoundException           Torrent hash is invalid
     * @throws InvalidArgumentException    Torrent name is empty
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#set-torrent-name
     */
    public function torrentRename(string $hash, string $name): static
    {
        $api = new Torrent\TorrentRename($hash, $name);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Torrent: Set torrent category.
     *
     * @param string $hashes   hashes can contain multiple hashes separated by `|` or set to `all`
     * @param string $category category is the torrent category you want to set
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     * @throws OperationFailedException    Category name does not exist
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#set-torrent-category
     */
    public function torrentSetCategory(string $hashes, string $category): static
    {
        $api = new Torrent\TorrentSetCategory($hashes, $category);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Torrent: Add torrent tags.
     *
     * @param string $hashes hashes can contain multiple hashes separated by `|` or set to `all`
     * @param string $tags   tags is the list of tags you want to add to passed torrents
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#add-torrent-tags
     */
    public function torrentAddTags(string $hashes, string $tags): static
    {
        $api = new Torrent\TorrentAddTags($hashes, $tags);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Torrent: Remove torrent tags.
     *
     * @param string $hashes hashes can contain multiple hashes separated by `|` or set to `all`
     * @param string $tags   tags is the list of tags you want to remove from passed torrents. Empty list removes all tags from relevant torrents.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#remove-torrent-tags
     */
    public function torrentRemoveTags(string $hashes, string $tags): static
    {
        $api = new Torrent\TorrentRemoveTags($hashes, $tags);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Torrent: Set automatic torrent management.
     *
     * @param string $hashes hashes can contain multiple hashes separated by `|` or set to `all`
     * @param bool   $enable enable is a boolean, affects the torrents listed in hashes, default is `false`
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#set-automatic-torrent-management
     */
    public function torrentSetAutoManagement(string $hashes, bool $enable): static
    {
        $api = new Torrent\TorrentSetAutoManagement($hashes, $enable);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Torrent: Toggle sequential download.
     *
     * @param string $hashes The hashes of the torrents you want to toggle sequential download for. hashes can contain multiple hashes separated by `|`, to toggle sequential download for multiple torrents, or set to `all`, to toggle sequential download for all torrents.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#toggle-sequential-download
     */
    public function torrentToggleSequentialDownload(string $hashes): static
    {
        $api = new Torrent\TorrentToggleSequentialDownload($hashes);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Torrent: Set first/last piece priority.
     *
     * @param string $hashes The hashes of the torrents you want to toggle the first/last piece priority for. hashes can contain multiple hashes separated by `|`, to toggle the first/last piece priority for multiple torrents, or set to `all`, to toggle the first/last piece priority for all torrents.
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#set-firstlast-piece-priority
     */
    public function torrentToggleFirstLastPiecePrio(string $hashes): static
    {
        $api = new Torrent\TorrentToggleFirstLastPiecePrio($hashes);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Torrent: Set force start.
     *
     * @param string $hashes hashes can contain multiple hashes separated by `|` or set to `all`
     * @param bool   $value  value is a boolean, affects the torrents listed in hashes, default is `false`
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#set-force-start
     */
    public function torrentSetForceStart(string $hashes, bool $value): static
    {
        $api = new Torrent\TorrentSetForceStart($hashes, $value);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Torrent: Set super seeding.
     *
     * @param string $hashes hashes can contain multiple hashes separated by `|` or set to `all`
     * @param bool   $value  value is a boolean, affects the torrents listed in hashes, default is false
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#set-super-seeding
     */
    public function torrentSetSuperSeeding(string $hashes, bool $value): static
    {
        $api = new Torrent\TorrentSetSuperSeeding($hashes, $value);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Torrent: Rename file.
     *
     * @param string $hash    The hash of the torrent
     * @param string $oldPath The old path of the torrent
     * @param string $newPath The new path to use for the file
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     * @throws NotFoundException           torrent hash was not found
     * @throws OperationFailedException    Invalid newPath or oldPath, or newPath already in use
     * @throws InvalidArgumentException    Missing newPath parameter
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#rename-file
     */
    public function torrentRenameFile(string $hash, string $oldPath, string $newPath): static
    {
        $api = new Torrent\TorrentRenameFile($hash, $oldPath, $newPath);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Torrent: Rename folder.
     *
     * @param string $hash    The hash of the torrent
     * @param string $oldPath The old path of the torrent
     * @param string $newPath The new path to use for the file
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     * @throws NotFoundException           torrent hash was not found
     * @throws OperationFailedException    Invalid newPath or oldPath, or newPath already in use
     * @throws InvalidArgumentException    Missing newPath parameter
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#rename-folder
     */
    public function torrentRenameFolder(string $hash, string $oldPath, string $newPath): static
    {
        $api = new Torrent\TorrentRenameFolder($hash, $oldPath, $newPath);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Torrent: Add new torrent from URLs.
     *
     *  `http://`, `https://`, `magnet:` and `bc://bt/` links are supported.
     *
     * @param string|string[] $urls
     * @param null|string     $savepath           Download folder
     * @param null|string     $cookie             Cookie sent to download the .torrent file
     * @param null|string     $category           Category for the torrent
     * @param null|string     $tags               Tags for the torrent, split by ','
     * @param null|string     $skip_checking      Skip hash checking. Possible values are `true`, `false` (default)
     * @param null|string     $paused             Add torrents in the paused state. Possible values are `true`, `false` (default)
     * @param null|string     $root_folder        Create the root folder. Possible values are `true`, `false`, unset (default)
     * @param null|string     $rename             Rename torrent
     * @param null|int        $upLimit            Set torrent upload speed limit. Unit in bytes/second
     * @param null|int        $dlLimit            Set torrent download speed limit. Unit in bytes/second
     * @param null|float      $ratioLimit         Set torrent share ratio limit
     * @param null|int        $seedingTimeLimit   Set torrent seeding time limit. Unit in seconds
     * @param null|bool       $autoTMM            Whether Automatic Torrent Management should be used
     * @param null|string     $sequentialDownload Enable sequential download. Possible values are `true`, `false` (default)
     * @param null|string     $firstLastPiecePrio Prioritize download first last piece. Possible values are `true`, `false` (default)
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     * @throws InvalidArgumentException    empty urls
     * @throws OperationFailedException    Torrent file is not valid
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#add-new-torrent
     */
    public function torrentAddFromUrls(
        string | array $urls,
        string $savepath = null,
        string $cookie = null,
        string $category = null,
        string $tags = null,
        string $skip_checking = null,
        string $paused = null,
        string $root_folder = null,
        string $rename = null,
        int $upLimit = null,
        int $dlLimit = null,
        float $ratioLimit = null,
        int $seedingTimeLimit = null,
        bool $autoTMM = null,
        string $sequentialDownload = null,
        string $firstLastPiecePrio = null
    ): static {
        $urls = \is_array($urls) ?: [$urls];
        $api = Torrent\TorrentAdd::fromUrls($urls, $savepath, $cookie, $category, $tags, $skip_checking, $paused, $root_folder, $rename, $upLimit, $dlLimit, $ratioLimit, $seedingTimeLimit, $autoTMM, $sequentialDownload, $firstLastPiecePrio);
        $this->client->execute($api);
        return $this;
    }

    /**
     * Torrent: Add new torrent from files.
     *
     * @param string|string[] $torrents           torrent file path
     * @param null|string     $savepath           Download folder
     * @param null|string     $cookie             Cookie sent to download the .torrent file
     * @param null|string     $category           Category for the torrent
     * @param null|string     $tags               Tags for the torrent, split by ','
     * @param null|string     $skip_checking      Skip hash checking. Possible values are `true`, `false` (default)
     * @param null|string     $paused             Add torrents in the paused state. Possible values are `true`, `false` (default)
     * @param null|string     $root_folder        Create the root folder. Possible values are `true`, `false`, unset (default)
     * @param null|string     $rename             Rename torrent
     * @param null|int        $upLimit            Set torrent upload speed limit. Unit in bytes/second
     * @param null|int        $dlLimit            Set torrent download speed limit. Unit in bytes/second
     * @param null|float      $ratioLimit         Set torrent share ratio limit
     * @param null|int        $seedingTimeLimit   Set torrent seeding time limit. Unit in seconds
     * @param null|bool       $autoTMM            Whether Automatic Torrent Management should be used
     * @param null|string     $sequentialDownload Enable sequential download. Possible values are `true`, `false` (default)
     * @param null|string     $firstLastPiecePrio Prioritize download first last piece. Possible values are `true`, `false` (default)
     *
     * @throws UnauthorizedException       unauthorized, login first
     * @throws UnexpectedResponseException unexpected qBt response
     * @throws InvalidArgumentException    empty torrents
     * @throws OperationFailedException    Torrent file is not valid
     *
     * @return $this
     *
     * @see https://github.com/qbittorrent/qBittorrent/wiki/WebUI-API-(qBittorrent-4.1)#add-new-torrent
     */
    public function torrentAddFromTorrents(
        string | array $torrents,
        string $savepath = null,
        string $cookie = null,
        string $category = null,
        string $tags = null,
        string $skip_checking = null,
        string $paused = null,
        string $root_folder = null,
        string $rename = null,
        int $upLimit = null,
        int $dlLimit = null,
        float $ratioLimit = null,
        int $seedingTimeLimit = null,
        bool $autoTMM = null,
        string $sequentialDownload = null,
        string $firstLastPiecePrio = null
    ): static {
        $torrents = \is_array($torrents) ?: [$torrents];
        $api = Torrent\TorrentAdd::fromTorrents($torrents, $savepath, $cookie, $category, $tags, $skip_checking, $paused, $root_folder, $rename, $upLimit, $dlLimit, $ratioLimit, $seedingTimeLimit, $autoTMM, $sequentialDownload, $firstLastPiecePrio);
        $this->client->execute($api);
        return $this;
    }
}
