<?php

declare(strict_types=1);

namespace YaSD\QBitTorrent;

function verifyJson(string $json): bool
{
    json_decode($json);
    return JSON_ERROR_NONE === json_last_error();
}
