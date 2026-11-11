<?php

namespace App\Services;

/**
 * Pure money math. No database, no framework: easy to test and reuse.
 */
final class InvoiceCalculator
{
    public static function lineAmount(float|string $quantity, float|string $unitPrice): float
    {
        return round((float) $quantity * (float) $unitPrice, 2);
    }

    /**
     * @param  iterable<int, array{quantity: float|string, unit_price: float|string}>  $items
     * @return array{subtotal: float, discount: float, tax: float, total: float}
     */
    public static function totals(iterable $items, float|string $discount, float|string $taxRate): array
    {
        $subtotal = round(collect($items)->sum(
            fn ($item) => self::lineAmount($item['quantity'], $item['unit_price'])
        ), 2);

        $appliedDiscount = min((float) $discount, $subtotal);
        $taxable = $subtotal - $appliedDiscount;
        $tax = round($taxable * (float) $taxRate / 100, 2);

        return [
            'subtotal' => $subtotal,
            'discount' => round($appliedDiscount, 2),
            'tax' => $tax,
            'total' => round($taxable + $tax, 2),
        ];
    }
}