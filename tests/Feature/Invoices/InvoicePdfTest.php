<?php

use App\Models\Invoice;
use App\Models\User;

it('lets the owner download their invoice as a pdf', function () {
    $owner = User::factory()->create();
    $invoice = Invoice::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($owner)
        ->get(route('invoices.pdf', $invoice))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

it('blocks other users from downloading it', function () {
    $invoice = Invoice::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('invoices.pdf', $invoice))
        ->assertForbidden();
});
