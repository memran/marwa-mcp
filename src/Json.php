<?php

declare(strict_types=1);

namespace Marwa\MCP;

use JsonException;

final class Json
{
    /**
     * @return mixed
     *
     * @throws JsonException
     */
    public static function decode(string $json): mixed
    {
        return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param mixed $value
     *
     * @throws JsonException
     */
    public static function encode(mixed $value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
    }
}
