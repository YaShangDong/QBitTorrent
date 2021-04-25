<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent\Exception;

use RuntimeException;
use Throwable;
use YaSD\QBitTorrent\Exception;

class NotFoundException extends RuntimeException implements Exception
{
    public static function forTorHash(string $hash, ?Throwable $previous = null): static
    {
        $msg = sprintf('Torrent_Not_Found: hash=%s', $hash);
        return new static($msg, 0, $previous);
    }

    public static function forSearchJob(int $jobId, ?Throwable $previous = null): static
    {
        $msg = sprintf('Search_Job_Not_Found: ID=%d', $jobId);
        return new static($msg, 0, $previous);
    }
}
