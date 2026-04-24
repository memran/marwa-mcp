<?php

declare(strict_types=1);

namespace Memran\MarwaMcp\Support;

final class Arr
{
    /**
     * @param array<mixed> $array
     */
    public static function isList(array $array): bool
    {
        return array_is_list($array);
    }

    /**
     * @param array<string, mixed> $array
     */
    public static function string(array $array, string $key, ?string $default = null): ?string
    {
        $value = $array[$key] ?? $default;

        return is_string($value) ? $value : $default;
    }
}
