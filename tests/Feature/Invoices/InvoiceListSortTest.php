<?php

use App\Livewire\Invoices\Index;
use App\Models\Invoice;
use App\Models\User;
use Livewire\Livewire;

it('lists the most recently created invoice first by default', function () {
    $user = User::factory()->create();

    Invoice::factory()->for($user)->create([
        'number' => 'INV-0001',
        'sequence' => 1,
        'issue_date' => now()->subDays(1),
    ]);

    Invoice::factory()->for($user)->create([
        'number' => 'INV-0002',
        'sequence' => 2,
        'issue_date' => now()->subDays(20),
    ]);

    $this->actingAs($user);

    Livewire::test(Index::class)
        ->assertSeeInOrder(['INV-0002', 'INV-0001']);
});

it('sorts by a column and reverses the order on a second click', function () {
    $user = User::factory()->create();

    Invoice::factory()->for($user)->create([
        'number' => 'INV-0001',
        'sequence' => 1,
        'total' => 900,
    ]);

    Invoice::factory()->for($user)->create([
        'number' => 'INV-0002',
        'sequence' => 2,
        'total' => 100,
    ]);

    $this->actingAs($user);

    Livewire::test(Index::class)
        ->call('sortBy', 'total')
        ->assertSet('sort', 'total')
        ->assertSet('direction', 'asc')
        ->assertSee('▲')
        ->assertSeeInOrder(['INV-0002', 'INV-0001'])
        ->call('sortBy', 'total')
        ->assertSet('direction', 'desc')
        ->assertSee('▼')
        ->assertSeeInOrder(['INV-0001', 'INV-0002']);
});

it('ignores sort columns that are not in the list', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Index::class)
        ->call('sortBy', 'password')
        ->assertSet('sort', '');
});

it('returns to the default order when reset', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Index::class)
        ->call('sortBy', 'due')
        ->call('resetSort')
        ->assertSet('sort', '')
        ->assertSet('direction', 'desc');
});

it('keeps currencies together when sorting by total', function () {
    $user = User::factory()->create();

    Invoice::factory()->for($user)->create([
        'number' => 'INV-0001',
        'sequence' => 1,
        'currency' => 'USD',
        'total' => 650,
    ]);

    Invoice::factory()->for($user)->create([
        'number' => 'INV-0002',
        'sequence' => 2,
        'currency' => 'EUR',
        'total' => 300,
    ]);

    Invoice::factory()->for($user)->create([
        'number' => 'INV-0003',
        'sequence' => 3,
        'currency' => 'USD',
        'total' => 100,
    ]);

    $this->actingAs($user);

    // EUR first, then the USD invoices in amount order: EUR 300, USD 100, USD 650.
    Livewire::test(Index::class)
        ->call('sortBy', 'total')
        ->assertSeeInOrder(['INV-0002', 'INV-0003', 'INV-0001']);
});
