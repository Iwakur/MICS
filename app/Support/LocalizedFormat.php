<?php

namespace App\Support;

use DateTimeInterface;
use IntlDateFormatter;
use NumberFormatter;

final class LocalizedFormat
{
    public static function date(DateTimeInterface|string|null $value): string
    {
        if ($value === null) {
            return '';
        }

        return self::dateFormatter(IntlDateFormatter::LONG, IntlDateFormatter::NONE)
            ->format(self::timestamp($value)) ?: '';
    }

    public static function dateTime(DateTimeInterface|string|null $value): string
    {
        if ($value === null) {
            return '';
        }

        return self::dateFormatter(IntlDateFormatter::LONG, IntlDateFormatter::SHORT)
            ->format(self::timestamp($value)) ?: '';
    }

    public static function month(DateTimeInterface|string|null $value): string
    {
        if ($value === null) {
            return '';
        }

        $formatter = self::dateFormatter(IntlDateFormatter::NONE, IntlDateFormatter::NONE);
        $formatter->setPattern('LLLL y');

        return $formatter->format(self::timestamp($value)) ?: '';
    }

    public static function number(int|float|string|null $value, int $decimals = 2): string
    {
        $formatter = new NumberFormatter(self::intlLocale(), NumberFormatter::DECIMAL);
        $formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, $decimals);
        $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $decimals);

        return $formatter->format((float) $value) ?: '0';
    }

    private static function dateFormatter(int $dateType, int $timeType): IntlDateFormatter
    {
        return new IntlDateFormatter(
            self::intlLocale(),
            $dateType,
            $timeType,
            config('app.timezone'),
            IntlDateFormatter::GREGORIAN,
        );
    }

    private static function intlLocale(): string
    {
        return app()->getLocale() === 'uk' ? 'uk_UA' : 'en_US';
    }

    private static function timestamp(DateTimeInterface|string|null $value): int
    {
        if ($value instanceof DateTimeInterface) {
            return $value->getTimestamp();
        }

        return strtotime((string) $value) ?: 0;
    }
}
