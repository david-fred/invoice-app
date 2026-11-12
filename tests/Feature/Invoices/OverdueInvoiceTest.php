<?php

use App\Models\Invoice;

it('flags a sent invoice with a balance past its due date as overdue', function () {
    $invoice = Invoice::factory()->sent()->create([
        'total' => 100,
        'due_date' => now()->subDay(),
    ]);

    expect($invoice->isOverdue())->toBeTrue();
});

it('does not flag an invoice that is not yet due', function () {
    $invoice = Invoice::factory()->sent()->create([
        'total' => 100,
        'due_date' => now()->addDay(),
    ]);

    expect($invoice->isOverdue())->toBeFalse();
});

it('does not flag a sent invoice that is paid in full', function () {
    $invoice = Invoice::factory()->sent()->create([
        'total' => 100,
        'amount_paid' => 100,
        'due_date' => now()->subDay(),
    ]);

    expect($invoice->isOverdue())->toBeFalse();
});

it('does not flag drafts, even when past due', function () {
    $invoice = Invoice::factory()->create([
        'total' => 100,
        'due_date' => now()->subDay(),
    ]);

    expect($invoice->isOverdue())->toBeFalse();
});

it('filters the list to overdue invoices only', function () {
    $overdue = Invoice::factory()->sent()->create([
        'total' => 100,
        'due_date' => now()->subDays(3),
    ]);

    Invoice::factory()->sent()->create([
        'total' => 100,
        'due_date' => now()->addDays(3),
    ]);

    Invoice::factory()->sent()->create([
        'total' => 100,
        'amount_paid' => 100,
        'due_date' => now()->subDays(3),
    ]);

    Invoice::factory()->create([
        'total' => 100,
        'due_date' => now()->subDays(3),
    ]);

    $ids = Invoice::query()->withStatus('overdue')->pluck('id')->all();

    expect($ids)->toEqual([$overdue->id]);
});
