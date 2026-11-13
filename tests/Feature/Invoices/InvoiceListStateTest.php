<?php

use App\Enums\InvoiceStatus;
use App\Livewire\Invoices\Index;
use App\Models\Invoice;
use App\Models\User;
use Livewire\Livewire;

it('hides cancelled invoices by default and shows them when asked', function () {
    $user = User::factory()->create();

    Invoice::factory()->sent()->for($user)->create(['number' => 'INV-0001', 'sequence' => 1]);
    Invoice::factory()->for($user)->create([
        'number' => 'INV-0002',
        'sequence' => 2,
        'status' => InvoiceStatus::Cancelled,
    ]);

    $this->actingAs($user);

    Livewire::test(Index::class)
        ->assertSee('INV-0001')
        ->assertDontSee('INV-0002')
        ->set('status', 'cancelled')
        ->assertSee('INV-0002')
        ->assertDontSee('INV-0001')
        ->set('status', 'all')
        ->assertSee('INV-0001')
        ->assertSee('INV-0002');
});

it('remembers the last filters when the list is opened again', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Index::class)->set('currency', 'EUR');

    Livewire::test(Index::class)->assertSet('currency', 'EUR');
});

it('builds a back link that keeps the current filters and sort', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Index::class)
        ->set('currency', 'EUR')
        ->set('sort', 'total');

    expect(Index::backUrl())
        ->toContain('currency=EUR')
        ->toContain('sort=total');
});

it('ignores a payment month that is not in the dropdown', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::withQueryParams(['paidMonth' => 'November'])
        ->test(Index::class)
        ->assertSet('paidMonth', '');
});

it('shows a reset order button only while a sort is active', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(Index::class)
        ->assertDontSee('Reset order')
        ->call('sortBy', 'total')
        ->assertSee('Reset order');
});
