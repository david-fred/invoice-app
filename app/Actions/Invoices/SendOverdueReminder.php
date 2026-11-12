<?php

namespace App\Actions\Invoices;

use App\Mail\InvoiceReminder;
use App\Models\Invoice;
use DomainException;
use Illuminate\Support\Facades\Mail;

class SendOverdueReminder
{
    public function handle(Invoice $invoice): void
    {
        if (! $invoice->isOverdue()) {
            throw new DomainException('Only overdue invoices can be reminded.');
        }

        $invoice->loadMissing('client');

        if (blank($invoice->client->email)) {
            throw new DomainException('This client has no email address.');
        }

        Mail::to($invoice->client->email)->send(new InvoiceReminder($invoice));

        $invoice->update(['reminded_at' => now()]);
    }
}
