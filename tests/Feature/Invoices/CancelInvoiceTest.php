<?php

use App\Actions\Invoices\CancelInvoice;
use App\Actions\Payments\RecordPayment;
use App\Enums\InvoiceStatus;
use App\Livewire\Invoices\Show;
use App\Models\Invoice;
use App\Models\User;
use DomainException;
use Livewire\Livewire;

it('cancels a sent invoice with no payments', function () {
    $invoice = Invoice::factory()->sent()->create(['total' => 100]);

    app(CancelInvoice::class)->handle($invoice);

    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Cancelled);
});

it('refuses to cancel a sent invoice that has payments', function () {
    $invoice = Invoice::factory()->sent()->create(['total' => 100, 'amount_paid' => 40]);

    expect(fn () => app(CancelInvoice::class)->handle($invoice))
        ->toThrow(DomainException::class);

    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Sent);
});

it('refuses to cancel a draft', function () {
    $invoice = Invoice::factory()->create(['total' => 100]);

    expect(fn () => app(CancelInvoice::class)->handle($invoice))
        ->toThrow(DomainException::class);
});

it('blocks payments on a cancelled invoice', function () {
    $invoice = Invoice::factory()->sent()->create(['total' => 100]);

    app(CancelInvoice::class)->handle($invoice);

    expect(fn () => app(RecordPayment::class)->handle($invoice, [
        'amount' => '10',
        'paid_on' => '2026-01-05',
    ]))->toThrow(DomainException::class);
});

it('never shows a cancelled invoice as overdue', function () {
    $invoice = Invoice::factory()->create([
        'status' => InvoiceStatus::Cancelled,
        'total' => 100,
        'due_date' => now()->subDays(3),
    ]);

    expect($invoice->isOverdue())->toBeFalse()
        ->and(Invoice::query()->withStatus('overdue')->count())->toBe(0);
});

it('cancels a sent invoice from the detail page', function () {
    $user = User::factory()->create();
    $invoice = Invoice::factory()->sent()->for($user)->create(['total' => 100]);

    $this->actingAs($user);

    Livewire::test(Show::class, ['invoice' => $invoice])
        ->call('cancel')
        ->assertHasNoErrors();

    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Cancelled);
});
