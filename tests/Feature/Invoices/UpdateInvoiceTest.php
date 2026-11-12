<?php

use App\Actions\Invoices\UpdateInvoice;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use DomainException;

it('replaces items and recalculates totals for a draft', function () {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $invoice = Invoice::factory()->for($user)->for($client)->create();

    InvoiceItem::factory()->for($invoice)->create([
        'description' => 'Old item',
        'quantity' => 1,
        'unit_price' => 999,
        'amount' => 999,
        'position' => 1,
    ]);

    app(UpdateInvoice::class)->handle($invoice, [
        'client_id' => $client->id,
        'issue_date' => '2026-01-01',
        'due_date' => '2026-01-15',
        'currency' => 'USD',
        'notes' => '',
        'discount' => '0',
        'tax_rate' => '10',
        'items' => [
            ['description' => 'Design', 'quantity' => '2', 'unit_price' => '100'],
            ['description' => 'Hosting', 'quantity' => '1', 'unit_price' => '50'],
        ],
    ]);

    $invoice->refresh();

    expect($invoice->items)->toHaveCount(2)
        ->and($invoice->items->pluck('description')->all())->toBe(['Design', 'Hosting'])
        ->and((float) $invoice->subtotal)->toBe(250.0)
        ->and((float) $invoice->total)->toBe(275.0);
});

it('refuses to edit a sent invoice and leaves it unchanged', function () {
    $invoice = Invoice::factory()->sent()->create(['total' => 500]);

    expect(fn () => app(UpdateInvoice::class)->handle($invoice, ['items' => []]))
        ->toThrow(DomainException::class);

    expect((float) $invoice->fresh()->total)->toBe(500.0);
});
