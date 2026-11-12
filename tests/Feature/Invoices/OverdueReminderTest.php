<?php

use App\Actions\Invoices\SendOverdueReminder;
use App\Mail\InvoiceReminder;
use App\Models\Client;
use App\Models\Invoice;
use DomainException;
use Illuminate\Support\Facades\Mail;

it('emails the client about an overdue invoice and records when', function () {
    Mail::fake();

    $client = Client::factory()->create(['email' => 'client@example.test']);
    $invoice = Invoice::factory()->sent()->create([
        'client_id' => $client->id,
        'total' => 100,
        'due_date' => now()->subDays(3),
    ]);

    $this->artisan('invoices:remind-overdue')->assertSuccessful();

    Mail::assertSent(InvoiceReminder::class, fn (InvoiceReminder $mail) => $mail->hasTo('client@example.test'));
    expect($invoice->fresh()->reminded_at)->not->toBeNull();
});

it('skips invoices whose client has no email address', function () {
    Mail::fake();

    $client = Client::factory()->create(['email' => null]);
    Invoice::factory()->sent()->create([
        'client_id' => $client->id,
        'total' => 100,
        'due_date' => now()->subDays(3),
    ]);

    $this->artisan('invoices:remind-overdue')->assertSuccessful();

    Mail::assertNothingSent();
});

it('does not remind the same invoice more than once a week', function () {
    Mail::fake();

    $client = Client::factory()->create(['email' => 'client@example.test']);
    Invoice::factory()->sent()->create([
        'client_id' => $client->id,
        'total' => 100,
        'due_date' => now()->subDays(3),
        'reminded_at' => now()->subDay(),
    ]);

    $this->artisan('invoices:remind-overdue')->assertSuccessful();

    Mail::assertNothingSent();
});

it('reminds again once a week has passed', function () {
    Mail::fake();

    $client = Client::factory()->create(['email' => 'client@example.test']);
    Invoice::factory()->sent()->create([
        'client_id' => $client->id,
        'total' => 100,
        'due_date' => now()->subDays(3),
        'reminded_at' => now()->subDays(8),
    ]);

    $this->artisan('invoices:remind-overdue')->assertSuccessful();

    Mail::assertSent(InvoiceReminder::class, 1);
});

it('ignores invoices that are not yet due or already paid', function () {
    Mail::fake();

    $client = Client::factory()->create(['email' => 'client@example.test']);

    Invoice::factory()->sent()->create([
        'client_id' => $client->id,
        'total' => 100,
        'due_date' => now()->addDays(3),
    ]);

    Invoice::factory()->sent()->create([
        'client_id' => $client->id,
        'total' => 100,
        'amount_paid' => 100,
        'due_date' => now()->subDays(3),
    ]);

    $this->artisan('invoices:remind-overdue')->assertSuccessful();

    Mail::assertNothingSent();
});

it('refuses to remind an invoice that is not overdue', function () {
    $invoice = Invoice::factory()->sent()->create([
        'total' => 100,
        'due_date' => now()->addDay(),
    ]);

    expect(fn () => app(SendOverdueReminder::class)->handle($invoice))
        ->toThrow(DomainException::class);
});
