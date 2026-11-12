<?php

use App\Actions\Invoices\DeleteInvoice;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use DomainException;

it('deletes a draft and its items', function () {
    $invoice = Invoice::factory()->create();

    InvoiceItem::factory()->for($invoice)->create([
        'description' => 'Design',
        'quantity' => 1,
        'unit_price' => 100,
        'amount' => 100,
        'position' => 1,
    ]);

    app(DeleteInvoice::class)->handle($invoice);

    expect(Invoice::find($invoice->id))->toBeNull()
        ->and(InvoiceItem::where('invoice_id', $invoice->id)->count())->toBe(0);
});

it('refuses to delete a sent invoice and keeps it', function () {
    $invoice = Invoice::factory()->sent()->create();

    expect(fn () => app(DeleteInvoice::class)->handle($invoice))
        ->toThrow(DomainException::class);

    expect(Invoice::find($invoice->id))->not->toBeNull();
});
