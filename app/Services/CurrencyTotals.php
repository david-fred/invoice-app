<?php

namespace App\Services;

/**
 * The only place that adds money together.
 *
 * Amounts are summed per currency. Different currencies are never added to each other.
 */
final class CurrencyTotals
{
    /**
     * @param  iterable<int, mixed>  $items
     * @param  callable(mixed): (float|int|string)  $amount
     * @param  callable(mixed): string  $currency
     * @return array<string, float> Totals keyed by currency code, sorted by code.
     */
    public static function sum(iterable $items, callable $amount, callable $currency): array
    {
        $totals = [];

        foreach ($items as $item) {
            $code = $currency($item);
            $totals[$code] = round(($totals[$code] ?? 0) + (float) $amount($item), 2);
        }

        ksort($totals);

        return $totals;
    }
}
