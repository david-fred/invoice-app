<?php

namespace App\Actions\Invoices;

use App\Enums\InvoiceStatus;
use App\Mail\InvoiceSent;
use App\Models\Invoice;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendInvoice
{
    public function handle(Invoice $invoice): void
    {
        DB::transaction(function () use ($invoice): void {
            // Lock the row so a double click can't send the invoice twice.
            /** @var Invoice $locked */
            $locked = Invoice::query()->whereKey($invoice->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== InvoiceStatus::Draft) {
                throw new DomainException('Only draft invoices can be sent.');
            }

            if ((float) $locked->total <= 0) {
                throw new DomainException('An invoice needs a total above zero before it can be sent.');
            }

            $locked->update([
                'status' => InvoiceStatus::Sent,
                'sent_at' => now(),
            ]);
        });

        // Reload after the commit, then email the client.
        $invoice->refresh()->loadMissing('client');

        if (filled($invoice->client->email)) {
            Mail::to($invoice->client->email)->send(new InvoiceSent($invoice));
        }
    }
}
