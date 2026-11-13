<?php

use App\Enums\InvoiceStatus;
use App\Livewire\Invoices\Index;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Livewire\Livewire;

it('filters the list by currency', function () {
    $user = User::factory()->create();

    Invoice::factory()->for($user)->create(['number' => 'INV-0001', 'sequence' => 1, 'currency' => 'USD']);
    Invoice::factory()->for($user)->create(['number' => 'INV-0002', 'sequence' => 2, 'currency' => 'EUR']);

    $this->actingAs($user);

    Livewire::test(Index::class)
        ->set('currency', 'EUR')
        ->assertSee('INV-0002')
        ->assertDontSee('INV-0001');
});

it('filters the list by issue date range', function () {
    $user = User::factory()->create();

    Invoice::factory()->for($user)->create([
        'number' => 'INV-0001',
        'sequence' => 1,
        'issue_date' => '2026-01-10',
    ]);

    Invoice::factory()->for($user)->create([
        'number' => 'INV-0002',
        'sequence' => 2,
        'issue_date' => '2026-03-05',
    ]);

    $this->actingAs($user);

    Livewire::test(Index::class)
        ->set('issuedFrom', '2026-02-01')
        ->set('issuedTo', '2026-03-31')
        ->assertSee('INV-0002')
        ->assertDontSee('INV-0001');
});

it('shows only invoices paid in the chosen month and totals what was received', function () {
    $user = User::factory()->create();

    $january = Invoice::factory()->sent()->for($user)->create([
        'number' => 'INV-0001',
        'sequence' => 1,
        'total' => 100,
        'amount_paid' => 40,
    ]);

    Payment::factory()->for($january)->create([
        'amount' => 40,
        'paid_on' => '2026-01-15',
    ]);

    $march = Invoice::factory()->for($user)->create([
        'number' => 'INV-0002',
        'sequence' => 2,
        'status' => InvoiceStatus::Paid,
        'total' => 100,
        'amount_paid' => 100,
    ]);

    Payment::factory()->for($march)->create([
        'amount' => 100,
        'paid_on' => '2026-03-02',
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Index::class)
        ->set('paidMonth', '2026-03');

    $component
        ->assertSee('INV-0002')
        ->assertDontSee('INV-0001');

    expect($component->instance()->summary()['received'])->toBe(['USD' => 100.0]);
});

it('totals each currency, and the Sent filter matches the dashboard outstanding figure', function () {
    $user = User::factory()->create();

    Invoice::factory()->sent()->for($user)->create([
        'currency' => 'USD',
        'total' => 100,
        'amount_paid' => 40,
        'due_date' => now()->addDays(5),
    ]);

    Invoice::factory()->sent()->for($user)->create([
        'currency' => 'USD',
        'total' => 50,
        'amount_paid' => 0,
        'due_date' => now()->subDays(2),
    ]);

    Invoice::factory()->sent()->for($user)->create([
        'currency' => 'EUR',
        'total' => 300,
        'amount_paid' => 0,
        'due_date' => now()->addDays(5),
    ]);

    // A draft is not outstanding and must not count.
    Invoice::factory()->for($user)->create([
        'currency' => 'USD',
        'total' => 999,
    ]);

    $this->actingAs($user);

    $summary = Livewire::test(Index::class)
        ->set('status', 'sent')
        ->set('currency', 'USD')
        ->instance()
        ->summary();

    expect($summary['balance'])->toBe(['USD' => 110.0])
        ->and($summary['count'])->toBe(['USD' => 2]);
});

it('clears every filter at once', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Index::class)
        ->set('currency', 'EUR')
        ->set('paidMonth', '2026-03')
        ->call('clearFilters')
        ->assertSet('currency', '')
        ->assertSet('paidMonth', '');
});
