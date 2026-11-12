<?php

namespace App\Actions\Invoices;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\InvoiceCalculator;

class RefreshInvoiceTotals
{
    public function handle(Invoice $invoice): void
    {
        $invoice->load('items');

        $items = $invoice->items
            ->map(fn (InvoiceItem $item) => [
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
            ])
            ->all();

        $invoice->update(InvoiceCalculator::totals(
            $items,
            $invoice->discount,
            $invoice->tax_rate,
        ));
    }
}
