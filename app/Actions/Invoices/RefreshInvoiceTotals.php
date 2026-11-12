<?php

namespace App\Actions\Invoices;

use App\Models\Invoice;
use App\Services\InvoiceCalculator;

class RefreshInvoiceTotals
{
    public function handle(Invoice $invoice): void
    {
        $invoice->load('items');

        $invoice->update(InvoiceCalculator::totals(
            $invoice->items,
            $invoice->discount,
            $invoice->tax_rate,
        ));
    }
}
