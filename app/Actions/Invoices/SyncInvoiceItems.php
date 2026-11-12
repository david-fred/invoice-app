<?php

namespace App\Actions\Invoices;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\InvoiceCalculator;

class SyncInvoiceItems
{
    public function handle(Invoice $invoice, array $items): void
    {
        InvoiceItem::query()->where('invoice_id', $invoice->id)->delete();

        foreach (array_values($items) as $index => $item) {
            $invoice->items()->create([
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'amount' => InvoiceCalculator::lineAmount($item['quantity'], $item['unit_price']),
                'position' => $index + 1,
            ]);
        }
    }
}
