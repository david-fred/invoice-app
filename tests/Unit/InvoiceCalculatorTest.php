<?php

use App\Services\InvoiceCalculator;

it('calculates a line amount', function () {
    expect(InvoiceCalculator::lineAmount('2', '150.50'))->toBe(301.0);
});

it('applies discount before tax', function () {
    $totals = InvoiceCalculator::totals(
        [
            ['quantity' => '2', 'unit_price' => '100'],
            ['quantity' => '1', 'unit_price' => '50'],
        ],
        discount: '20',
        taxRate: '10',
    );

    // subtotal 250, minus discount 20 = 230, plus 10% tax (23) = 253
    expect($totals)->toMatchArray([
        'subtotal' => 250.0,
        'discount' => 20.0,
        'tax' => 23.0,
        'total' => 253.0,
    ]);
});

it('never discounts more than the subtotal', function () {
    $totals = InvoiceCalculator::totals(
        [['quantity' => '1', 'unit_price' => '40']],
        discount: '100',
        taxRate: '0',
    );

    expect($totals['total'])->toBe(0.0);
});
