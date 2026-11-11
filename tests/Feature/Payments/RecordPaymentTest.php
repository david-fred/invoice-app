<?php

use App\Actions\Payments\RecordPayment;
use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use DomainException;
use Illuminate\Validation\ValidationException;

it('marks an invoice paid once fully paid', function () {
    $invoice = Invoice::factory()->sent()->create(['total' => 100]);
    $action = app(RecordPayment::class);

    $action->handle($invoice, ['amount' => '60', 'paid_on' => '2026-01-05']);
    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Sent);

    $action->handle($invoice, ['amount' => '40', 'paid_on' => '2026-01-06']);
    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Paid)
        ->and((float) $invoice->fresh()->amount_paid)->toBe(100.0);
});

it('rejects a payment larger than the balance', function () {
    $invoice = Invoice::factory()->sent()->create(['total' => 100]);

    app(RecordPayment::class)->handle($invoice, ['amount' => '150', 'paid_on' => '2026-01-05']);
})->throws(ValidationException::class);

it('refuses payments on draft invoices', function () {
    $invoice = Invoice::factory()->create(['total' => 100]);

    app(RecordPayment::class)->handle($invoice, ['amount' => '10', 'paid_on' => '2026-01-05']);
})->throws(DomainException::class);