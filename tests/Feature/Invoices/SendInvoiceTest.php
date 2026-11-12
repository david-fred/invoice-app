<?php

use App\Actions\Invoices\SendInvoice;
use App\Enums\InvoiceStatus;
use App\Mail\InvoiceSent;
use App\Models\Invoice;
use DomainException;
use Illuminate\Support\Facades\Mail;

it('marks a draft as sent and emails the client', function () {
    Mail::fake();
    $invoice = Invoice::factory()->create();

    app(SendInvoice::class)->handle($invoice);

    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Sent);
    Mail::assertSent(InvoiceSent::class);
});

it('refuses to send an invoice twice', function () {
    Mail::fake();
    $invoice = Invoice::factory()->sent()->create();

    app(SendInvoice::class)->handle($invoice);
})->throws(DomainException::class);
