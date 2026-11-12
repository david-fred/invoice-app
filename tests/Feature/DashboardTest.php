<?php

use App\Enums\InvoiceStatus;
use App\Livewire\Dashboard;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Livewire\Livewire;

it('redirects guests away from the dashboard', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

it('reports zeros when the user has no invoices', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(Dashboard::class)
        ->assertSee('No invoices yet.');

    expect($component->instance()->outstanding())->toBe([])
        ->and($component->instance()->overdueTotals())->toBe([])
        ->and($component->instance()->overdueCount())->toBe(0)
        ->and($component->instance()->paidThisMonth())->toBe([])
        ->and($component->instance()->invoicesPaidThisMonth())->toBe(0);
});

it('calculates figures for the signed-in user only', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    // Sent, 100 total with 40 paid: owes 60, not overdue.
    $partlyPaid = Invoice::factory()->sent()->for($user)->create([
        'total' => 100,
        'amount_paid' => 40,
        'due_date' => now()->addDays(5),
    ]);

    // Sent, 50 total, nothing paid, past due: owes 50, overdue.
    Invoice::factory()->sent()->for($user)->create([
        'total' => 50,
        'amount_paid' => 0,
        'due_date' => now()->subDays(2),
    ]);

    // Fully paid this month.
    Invoice::factory()->for($user)->create([
        'status' => InvoiceStatus::Paid,
        'total' => 30,
        'amount_paid' => 30,
        'paid_at' => now(),
        'due_date' => now()->subDays(2),
    ]);

    // Another user's invoice must not affect these figures.
    Invoice::factory()->sent()->for($other)->create([
        'total' => 999,
        'due_date' => now()->subDays(2),
    ]);

    Payment::factory()->for($partlyPaid)->create([
        'amount' => 40,
        'paid_on' => now()->toDateString(),
    ]);

    $component = Livewire::actingAs($user)->test(Dashboard::class)
        ->assertSee('Outstanding');

    expect($component->instance()->outstanding())->toBe(['USD' => 110.0])
        ->and($component->instance()->overdueTotals())->toBe(['USD' => 50.0])
        ->and($component->instance()->overdueCount())->toBe(1)
        ->and($component->instance()->paidThisMonth())->toBe(['USD' => 40.0])
        ->and($component->instance()->invoicesPaidThisMonth())->toBe(1);
});

it('keeps currencies separate in the totals', function () {
    $user = User::factory()->create();

    Invoice::factory()->sent()->for($user)->create([
        'currency' => 'USD',
        'total' => 100,
        'amount_paid' => 0,
        'due_date' => now()->addDays(5),
    ]);

    Invoice::factory()->sent()->for($user)->create([
        'currency' => 'EUR',
        'total' => 50,
        'amount_paid' => 0,
        'due_date' => now()->addDays(5),
    ]);

    $component = Livewire::actingAs($user)->test(Dashboard::class);

    expect($component->instance()->outstanding())
        ->toEqualCanonicalizing(['USD' => 100.0, 'EUR' => 50.0]);
});

it('keeps overdue and paid totals separate for each currency', function () {
    $user = User::factory()->create();

    // Overdue: USD 100 and EUR 40 must stay apart.
    Invoice::factory()->sent()->for($user)->create([
        'currency' => 'USD',
        'total' => 100,
        'amount_paid' => 0,
        'due_date' => now()->subDays(2),
    ]);

    Invoice::factory()->sent()->for($user)->create([
        'currency' => 'EUR',
        'total' => 40,
        'amount_paid' => 0,
        'due_date' => now()->subDays(2),
    ]);

    // Paid this month: EUR 60 fully paid, USD 25 part paid and not overdue.
    $paidInFull = Invoice::factory()->sent()->for($user)->create([
        'currency' => 'EUR',
        'total' => 60,
        'amount_paid' => 60,
        'due_date' => now()->subDays(2),
    ]);

    $partPaid = Invoice::factory()->sent()->for($user)->create([
        'currency' => 'USD',
        'total' => 100,
        'amount_paid' => 25,
        'due_date' => now()->addDays(5),
    ]);

    Payment::factory()->for($paidInFull)->create([
        'amount' => 60,
        'paid_on' => now()->toDateString(),
    ]);

    Payment::factory()->for($partPaid)->create([
        'amount' => 25,
        'paid_on' => now()->toDateString(),
    ]);

    $component = Livewire::actingAs($user)->test(Dashboard::class);

    expect($component->instance()->overdueTotals())->toBe(['EUR' => 40.0, 'USD' => 100.0])
        ->and($component->instance()->paidThisMonth())->toBe(['EUR' => 60.0, 'USD' => 25.0]);
});
