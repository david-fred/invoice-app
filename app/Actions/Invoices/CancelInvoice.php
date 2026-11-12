<?php

namespace App\Actions\Invoices;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use DomainException;
use Illuminate\Support\Facades\DB;

class CancelInvoice
{
    public function handle(Invoice $invoice): void
    {
        DB::transaction(function () use ($invoice): void {
            // Lock the row so a payment can't land while we check it.
            /** @var Invoice $locked */
            $locked = Invoice::query()->whereKey($invoice->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== InvoiceStatus::Sent) {
                throw new DomainException('Only sent invoices can be cancelled.');
            }

            if ((float) $locked->amount_paid > 0) {
                throw new DomainException('Invoices with payments cannot be cancelled.');
            }

            $locked->update(['status' => InvoiceStatus::Cancelled]);
        });
    }
}
