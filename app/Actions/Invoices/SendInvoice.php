<?php

namespace App\Actions\Invoices;

use App\Enums\InvoiceStatus;
use App\Mail\InvoiceSent;
use App\Models\Invoice;
use DomainException;
use Illuminate\Support\Facades\Mail;

class SendInvoice
{
    public function handle(Invoice $invoice): void
    {
        throw_unless(
            $invoice->status === InvoiceStatus::Draft,
            new DomainException('Only draft invoices can be sent.'),
        );

        $invoice->update([
            'status' => InvoiceStatus::Sent,
            'sent_at' => now(),
        ]);

        if (filled($invoice->client->email)) {
            Mail::to($invoice->client->email)->send(new InvoiceSent($invoice));
        }
    }
}