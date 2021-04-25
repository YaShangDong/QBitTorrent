<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Exception;

use RuntimeException;
use Throwable;
use YaSD\QBitTorrent\Exception;

class OperationFailedException extends RuntimeException implements Exception
{
    public static function fromRssAddFolder409(?Throwable $previous = null): static
    {
        $msg = 'Operation_Failed: failure to add rss folder';
        return new static($msg, 0, $previous);
    }

    public static function fromRssAddFeed409(?Throwable $previous = null): static
    {
        $msg = 'Operation_Failed: failure to add rss feed';
        return new static($msg, 0, $previous);
    }

    public static function fromRssRemoveItem409(?Throwable $previous = null): static
    {
        $msg = 'Operation_Failed: failure to remove rss item';
        return new static($msg, 0, $previous);
    }

    public static function fromRssMoveItem409(?Throwable $previous = null): static
    {
        $msg = 'Operation_Failed: failure to move rss item';
        return new static($msg, 0, $previous);
    }

    public static function fromSearchStartJob409(?Throwable $previous = null): static
    {
        $msg = 'Operation_Failed: User has reached the limit of max Running searches (currently set to 5)';
        return new static($msg, 0, $previous);
    }

    public static function fromTorrentEditTracker409(?Throwable $previous = null): static
    {
        $msg = 'Operation_Failed: origUrl was not found / newUrl already exists for the torrent';
        return new static($msg, 0, $previous);
    }

    public static function fromTorrentRemoveTrackers409(?Throwable $previous = null): static
    {
        $msg = 'Operation_Failed: all trackers urls were not found';
        return new static($msg, 0, $previous);
    }

    public static function forTorrentPriority(?Throwable $previous = null): static
    {
        $msg = 'Operation_Failed: torrent queueing is not enabled';
        return new static($msg, 0, $previous);
    }

    public static function fromTorrentAdd415(?Throwable $previous = null): static
    {
        $msg = 'Torrent_Add_Failed: torrent file is not valid';
        return new static($msg, 0, $previous);
    }
}
