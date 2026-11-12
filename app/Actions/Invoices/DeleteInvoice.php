<?php

namespace App\Actions\Invoices;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use DomainException;

class DeleteInvoice
{
    public function handle(Invoice $invoice): void
    {
        if ($invoice->status !== InvoiceStatus::Draft) {
            throw new DomainException('Only draft invoices can be deleted.');
        }

        $invoice->delete(); // items cascade via the foreign key
    }
}
