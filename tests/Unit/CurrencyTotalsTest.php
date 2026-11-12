<?php

use App\Services\CurrencyTotals;

it('never adds different currencies together', function () {
    $items = [
        ['currency' => 'USD', 'amount' => 100],
        ['currency' => 'EUR', 'amount' => 50],
        ['currency' => 'USD', 'amount' => 25.5],
    ];

    $totals = CurrencyTotals::sum(
        $items,
        fn (array $item) => $item['amount'],
        fn (array $item) => $item['currency'],
    );

    expect($totals)->toBe(['EUR' => 50.0, 'USD' => 125.5]);
});

it('returns an empty array when there is nothing to total', function () {
    expect(CurrencyTotals::sum([], fn () => 0, fn () => 'USD'))->toBe([]);
});
