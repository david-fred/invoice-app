<?php

namespace App\Actions\Invoices;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use DomainException;
use Illuminate\Support\Facades\DB;

class DeleteInvoice
{
    public function handle(Invoice $invoice): void
    {
        DB::transaction(function () use ($invoice): void {
            // Lock the row so a send or payment in another tab can't land between the check and the delete.
            /** @var Invoice $locked */
            $locked = Invoice::query()->whereKey($invoice->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== InvoiceStatus::Draft) {
                throw new DomainException('Only draft invoices can be deleted.');
            }

            $locked->delete(); // items cascade via the foreign key
        });
    }
}
