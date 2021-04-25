<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Exception;

use RuntimeException;
use Throwable;
use YaSD\QBitTorrent\Exception;

class InvalidArgumentException extends RuntimeException implements Exception
{
    public static function forInvalidUrl(string $url, ?Throwable $previous = null): static
    {
        $msg = sprintf('Invalid_Url: %s', $url);
        return new static($msg, 0, $previous);
    }

    public static function fromTorrentAddPeers(string $peers, ?Throwable $previous = null): static
    {
        $msg = sprintf('Invalid_Peers: %s', $peers);
        return new static($msg, 0, $previous);
    }

    public static function forTorrentName(string $torName, ?Throwable $previous = null): static
    {
        $msg = sprintf('Invalid_Torrent_Name: %s', $torName);
        return new static($msg, 0, $previous);
    }

    public static function fromTorrentAdd(?Throwable $previous = null): static
    {
        $msg = 'No_Valid_Torrents: both urls and torrents are empty';
        return new static($msg, 0, $previous);
    }

    public static function forInvalidJson(string $json, ?Throwable $previous = null): static
    {
        $msg = sprintf('Invalid_JSON: %s', $json);
        return new static($msg, 0, $previous);
    }

    public static function forSearchResultsOffset(int $offset, ?Throwable $previous = null): static
    {
        $msg = sprintf('Invalid_Search_Results_Offset: %d', $offset);
        return new static($msg, 0, $previous);
    }

    public static function forInvalidCategory(string $categoryName, ?Throwable $previous = null): static
    {
        $msg = sprintf('Invalid_Category_Name: %s', $categoryName);
        return new static($msg, 0, $previous);
    }
}
