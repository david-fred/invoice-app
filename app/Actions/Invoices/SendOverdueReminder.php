<?php

namespace App\Actions\Invoices;

use App\Jobs\SendInvoiceReminder;
use App\Models\Invoice;
use DomainException;

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

        // The job sends the email and records reminded_at only after the mail goes out.
        SendInvoiceReminder::dispatch($invoice->id);
    }
}
