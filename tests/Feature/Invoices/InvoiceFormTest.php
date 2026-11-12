<?php

use App\Livewire\Invoices\Form;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->client = Client::factory()->for($this->user)->create();
    $this->actingAs($this->user);
});

it('creates an invoice from the form', function () {
    Livewire::test(Form::class)
        ->set('form.client_id', (string) $this->client->id)
        ->set('form.issue_date', '2026-01-01')
        ->set('form.due_date', '2026-01-15')
        ->set('form.items.0.description', 'Consulting')
        ->set('form.items.0.quantity', '2')
        ->set('form.items.0.unit_price', '100')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect();

    expect(Invoice::where('user_id', $this->user->id)->count())->toBe(1);
});

it('rejects a due date before the issue date', function () {
    Livewire::test(Form::class)
        ->set('form.client_id', (string) $this->client->id)
        ->set('form.issue_date', '2026-02-10')
        ->set('form.due_date', '2026-02-01')
        ->set('form.items.0.description', 'Consulting')
        ->call('save')
        ->assertHasErrors(['form.due_date']);
});

it('updates the live total as items change', function () {
    Livewire::test(Form::class)
        ->set('form.items.0.quantity', '3')
        ->set('form.items.0.unit_price', '10')
        ->assertSee('30.00');
});
