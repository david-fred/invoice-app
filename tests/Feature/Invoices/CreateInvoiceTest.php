<?php

use App\Actions\Invoices\CreateInvoice;
use App\Enums\InvoiceStatus;
use App\Models\Client;
use App\Models\User;

it('creates a draft with items, totals and sequential numbers', function () {
    $user = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $action = app(CreateInvoice::class);

    $data = [
        'client_id' => $client->id,
        'issue_date' => '2026-01-01',
        'due_date' => '2026-01-15',
        'currency' => 'USD',
        'notes' => '',
        'discount' => '0',
        'tax_rate' => '10',
        'items' => [
            ['description' => 'Design', 'quantity' => '2', 'unit_price' => '100'],
            ['description' => 'Hosting', 'quantity' => '1', 'unit_price' => '50'],
        ],
    ];

    $first = $action->handle($user, $data);
    $second = $action->handle($user, $data);

    expect($first->status)->toBe(InvoiceStatus::Draft)
        ->and($first->number)->toBe('INV-0001')
        ->and($second->number)->toBe('INV-0002')
        ->and((float) $first->total)->toBe(275.0)
        ->and($first->items)->toHaveCount(2);
});
