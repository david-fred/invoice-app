<?php

use App\Actions\Clients\CreateClient;
use App\Actions\Clients\DeleteClient;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use DomainException;

it('creates a client for the given user', function () {
    $user = User::factory()->create();

    $client = app(CreateClient::class)->handle($user, [
        'name' => 'Acme Ltd',
        'email' => 'billing@acme.test',
        'phone' => '',
        'address' => '',
    ]);

    expect($client->user_id)->toBe($user->id)
        ->and($client->name)->toBe('Acme Ltd');
});

it('refuses to delete a client that has invoices', function () {
    $client = Client::factory()->create();
    Invoice::factory()->create(['client_id' => $client->id]);

    app(DeleteClient::class)->handle($client);
})->throws(DomainException::class);