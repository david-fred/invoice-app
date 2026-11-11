<?php

use App\Models\Invoice;
use App\Models\User;

it('only lets the owner edit a draft', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $draft = Invoice::factory()->create(['user_id' => $owner->id]);

    expect($owner->can('update', $draft))->toBeTrue()
        ->and($other->can('update', $draft))->toBeFalse();
});

it('locks sent invoices from editing', function () {
    $owner = User::factory()->create();
    $sent = Invoice::factory()->sent()->create(['user_id' => $owner->id]);

    expect($owner->can('update', $sent))->toBeFalse();
});