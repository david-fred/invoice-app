<?php

use App\Actions\Invoices\DeleteInvoice;
use App\Livewire\Clients\Form as ClientForm;
use App\Livewire\Invoices\Form as InvoiceForm;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use DomainException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

it('refuses to save a client that no longer belongs to the signed-in user', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $client = Client::factory()->for($owner)->create(['name' => 'Original']);

    $component = Livewire::actingAs($owner)->test(ClientForm::class, ['client' => $client]);

    Client::query()->whereKey($client->id)->update(['user_id' => $intruder->id]);

    expect(fn () => $component->set('form.name', 'Hacked')->call('save'))
        ->toThrow(ModelNotFoundException::class);

    expect($client->fresh()->name)->toBe('Original');
});

it('refuses to save an invoice that no longer belongs to the signed-in user', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();

    // A valid invoice: the owner's client and at least one line item. The form requires both,
    // so without them validation stops save() before the ownership check runs.
    $client = Client::factory()->for($owner)->create();
    // Refresh so the model carries the database defaults (discount, tax_rate) the form requires.
    $invoice = Invoice::factory()->for($owner)->for($client)->create(['notes' => 'Original'])->refresh();

    InvoiceItem::factory()->for($invoice)->create([
        'description' => 'Design',
        'quantity' => 1,
        'unit_price' => 100,
        'amount' => 100,
        'position' => 1,
    ]);

    // Control: the owner's normal save must pass validation, or the attack below proves nothing.
    Livewire::actingAs($owner)
        ->test(InvoiceForm::class, ['invoice' => $invoice])
        ->call('save')
        ->assertHasNoErrors();

    $component = Livewire::actingAs($owner)->test(InvoiceForm::class, ['invoice' => $invoice]);

    Invoice::query()->whereKey($invoice->id)->update(['user_id' => $intruder->id]);

    expect(fn () => $component->set('form.notes', 'Hacked')->call('save'))
        ->toThrow(ModelNotFoundException::class);

    expect($invoice->fresh()->notes)->toBe('Original');
});

it('refuses to delete a draft that was sent in another tab', function () {
    $owner = User::factory()->create();
    $invoice = Invoice::factory()->for($owner)->create();

    // Simulate the other tab sending the invoice after this one loaded it.
    Invoice::query()->whereKey($invoice->id)->update(['status' => 'sent', 'total' => 100]);

    expect(fn () => app(DeleteInvoice::class)->handle($invoice))
        ->toThrow(DomainException::class);

    expect(Invoice::query()->whereKey($invoice->id)->exists())->toBeTrue();
});
