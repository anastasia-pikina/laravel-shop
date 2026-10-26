<?php

namespace App\Support;

use InvalidArgumentException;

final class Money
{
    public const MAX_KOPEKS = 999_999_999_999;

    public static function toKopeks(string|int $amount): int
    {
        $amount = (string) $amount;
        if (! preg_match('/^(0|[1-9][0-9]*)(?:\.([0-9]{1,2}))?$/', $amount, $parts)) {
            throw new InvalidArgumentException('Invalid monetary amount.');
        }

        return ((int) $parts[1] * 100) + (int) str_pad($parts[2] ?? '', 2, '0');
    }

    public static function fromKopeks(int $amount): string
    {
        return sprintf('%d.%02d', intdiv($amount, 100), $amount % 100);
    }
}
