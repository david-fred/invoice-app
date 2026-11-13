<?php

use App\Actions\Invoices\SendOverdueReminder;
use App\Jobs\SendInvoiceReminder;
use App\Mail\InvoiceReminder;
use App\Models\Client;
use App\Models\Invoice;
use DomainException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

it('queues a reminder for each overdue invoice whose client has an email', function () {
    Queue::fake();

    $client = Client::factory()->create(['email' => 'client@example.test']);
    Invoice::factory()->sent()->create([
        'client_id' => $client->id,
        'total' => 100,
        'due_date' => now()->subDays(3),
    ]);

    $this->artisan('invoices:remind-overdue')->assertSuccessful();

    Queue::assertPushed(SendInvoiceReminder::class, 1);
});

it('does not queue a reminder when the client has no email address', function () {
    Queue::fake();

    $client = Client::factory()->create(['email' => null]);
    Invoice::factory()->sent()->create([
        'client_id' => $client->id,
        'total' => 100,
        'due_date' => now()->subDays(3),
    ]);

    $this->artisan('invoices:remind-overdue')->assertSuccessful();

    Queue::assertNothingPushed();
});

it('does not queue a second reminder within a week', function () {
    Queue::fake();

    $client = Client::factory()->create(['email' => 'client@example.test']);
    Invoice::factory()->sent()->create([
        'client_id' => $client->id,
        'total' => 100,
        'due_date' => now()->subDays(3),
        'reminded_at' => now()->subDay(),
    ]);

    $this->artisan('invoices:remind-overdue')->assertSuccessful();

    Queue::assertNothingPushed();
});

it('queues a reminder again once a week has passed', function () {
    Queue::fake();

    $client = Client::factory()->create(['email' => 'client@example.test']);
    Invoice::factory()->sent()->create([
        'client_id' => $client->id,
        'total' => 100,
        'due_date' => now()->subDays(3),
        'reminded_at' => now()->subDays(8),
    ]);

    $this->artisan('invoices:remind-overdue')->assertSuccessful();

    Queue::assertPushed(SendInvoiceReminder::class, 1);
});

it('ignores invoices that are not yet due or already paid', function () {
    Queue::fake();

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

    Queue::assertNothingPushed();
});

it('refuses to queue a reminder for an invoice that is not overdue', function () {
    $invoice = Invoice::factory()->sent()->create([
        'total' => 100,
        'due_date' => now()->addDay(),
    ]);

    expect(fn () => app(SendOverdueReminder::class)->handle($invoice))
        ->toThrow(DomainException::class);
});

it('dispatches the reminder job for the invoice', function () {
    Queue::fake();

    $client = Client::factory()->create(['email' => 'client@example.test']);
    $invoice = Invoice::factory()->sent()->create([
        'client_id' => $client->id,
        'total' => 100,
        'due_date' => now()->subDays(3),
    ]);

    app(SendOverdueReminder::class)->handle($invoice);

    Queue::assertPushed(
        SendInvoiceReminder::class,
        fn (SendInvoiceReminder $job) => $job->invoiceId === $invoice->id,
    );
});

it('sends the reminder and records when it was sent', function () {
    Mail::fake();

    $client = Client::factory()->create(['email' => 'client@example.test']);
    $invoice = Invoice::factory()->sent()->create([
        'client_id' => $client->id,
        'total' => 100,
        'due_date' => now()->subDays(3),
    ]);

    (new SendInvoiceReminder($invoice->id))->handle();

    Mail::assertSent(InvoiceReminder::class, fn (InvoiceReminder $mail) => $mail->hasTo('client@example.test'));
    expect($invoice->fresh()->reminded_at)->not->toBeNull();
});

it('sends nothing if the invoice was paid after the job was queued', function () {
    Mail::fake();

    $client = Client::factory()->create(['email' => 'client@example.test']);
    $invoice = Invoice::factory()->sent()->create([
        'client_id' => $client->id,
        'total' => 100,
        'amount_paid' => 100,
        'due_date' => now()->subDays(3),
    ]);

    (new SendInvoiceReminder($invoice->id))->handle();

    Mail::assertNothingSent();
    expect($invoice->fresh()->reminded_at)->toBeNull();
});

it('sends nothing if the client removed their email after the job was queued', function () {
    Mail::fake();

    $client = Client::factory()->create(['email' => null]);
    $invoice = Invoice::factory()->sent()->create([
        'client_id' => $client->id,
        'total' => 100,
        'due_date' => now()->subDays(3),
    ]);

    (new SendInvoiceReminder($invoice->id))->handle();

    Mail::assertNothingSent();
});

it('sends nothing if the invoice was already reminded this week', function () {
    Mail::fake();

    $client = Client::factory()->create(['email' => 'client@example.test']);
    $invoice = Invoice::factory()->sent()->create([
        'client_id' => $client->id,
        'total' => 100,
        'due_date' => now()->subDays(3),
        'reminded_at' => now()->subDay(),
    ]);

    (new SendInvoiceReminder($invoice->id))->handle();

    Mail::assertNothingSent();
});
