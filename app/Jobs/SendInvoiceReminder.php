<?php

namespace App\Jobs;

use App\Mail\InvoiceReminder;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;

class SendInvoiceReminder implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, Queueable;

    public int $tries = 3;

    public function __construct(public int $invoiceId) {}

    public function uniqueId(): string
    {
        return (string) $this->invoiceId;
    }

    public function handle(): void
    {
        $invoice = Invoice::query()->with('client')->find($this->invoiceId);

        // Re-check the rules: the invoice may have been paid, cancelled or reminded since this job was queued.
        if (! $invoice || ! $invoice->isOverdue() || blank($invoice->client->email)) {
            return;
        }

        if ($invoice->reminded_at?->gt(now()->subDays(7))) {
            return;
        }

        Mail::to($invoice->client->email)->send(new InvoiceReminder($invoice));

        // Recorded only after Mail::send() returns without an exception.
        $invoice->update(['reminded_at' => now()]);
    }
}
