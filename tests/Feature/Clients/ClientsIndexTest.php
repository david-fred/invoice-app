<?php

use App\Livewire\Clients\Index;
use App\Models\Client;
use App\Models\User;
use Livewire\Livewire;

it('shows email, phone and address in the clients table', function () {
    $user = User::factory()->create();

    Client::factory()->for($user)->create([
        'name' => 'Acme Ltd',
        'email' => 'billing@acme.test',
        'phone' => '+1 555 0100',
        'address' => '12 Marina Road, Test City',
    ]);

    $this->actingAs($user);

    Livewire::test(Index::class)
        ->assertSee('billing@acme.test')
        ->assertSee('+1 555 0100')
        ->assertSee('12 Marina Road, Test City');
});
