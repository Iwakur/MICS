<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Exact two-decimal money arithmetic using integer minor units (cents).
 */
final class Money
{
    public static function cents(string|int|float|null $amount): int
    {
        if ($amount === null) {
            return 0;
        }

        $decimal = is_float($amount)
            ? number_format($amount, 2, '.', '')
            : trim((string) $amount);

        if (! preg_match('/^-?\d+(?:\.\d{1,2})?$/', $decimal)) {
            throw new InvalidArgumentException("Invalid money value [{$decimal}].");
        }

        $negative = str_starts_with($decimal, '-');
        $unsigned = ltrim($decimal, '-');
        [$whole, $fraction] = array_pad(explode('.', $unsigned, 2), 2, '');
        $cents = ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');

        return $negative ? -$cents : $cents;
    }

    public static function decimal(int $cents): string
    {
        $sign = $cents < 0 ? '-' : '';
        $absolute = abs($cents);

        return sprintf('%s%d.%02d', $sign, intdiv($absolute, 100), $absolute % 100);
    }

    public static function display(int $cents): float
    {
        return $cents / 100;
    }
}
