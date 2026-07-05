<?php

namespace Tests\Unit;

use App\Support\Money;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public static function validAmounts(): array
    {
        return [
            'whole number' => ['10', 1000],
            'one decimal' => ['10.1', 1010],
            'two decimals' => ['10.10', 1010],
            'negative cents' => ['-0.05', -5],
            'zero' => [null, 0],
        ];
    }

    #[DataProvider('validAmounts')]
    public function test_it_converts_decimal_amounts_to_exact_cents(?string $amount, int $expected): void
    {
        $this->assertSame($expected, Money::cents($amount));
    }

    public function test_it_formats_cents_for_decimal_database_columns(): void
    {
        $this->assertSame('123.45', Money::decimal(12345));
        $this->assertSame('-0.05', Money::decimal(-5));
    }

    public function test_it_rejects_more_than_two_decimal_places(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::cents('10.001');
    }
}
