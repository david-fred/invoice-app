<?php

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

    expect($component->instance()->outstanding())->toBe(0.0)
        ->and($component->instance()->overdueCount())->toBe(0)
        ->and($component->instance()->paidThisMonth())->toBe(0.0);
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

    expect($component->instance()->outstanding())->toBe(110.0)
        ->and($component->instance()->overdueCount())->toBe(1)
        ->and($component->instance()->paidThisMonth())->toBe(40.0);
});
