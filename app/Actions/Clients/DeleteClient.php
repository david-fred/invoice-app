<?php

namespace App\Actions\Clients;

use App\Models\Client;
use DomainException;

class DeleteClient
{
    public function handle(Client $client): void
    {
        throw_if(
            $client->invoices()->exists(),
            new DomainException('This client has invoices and cannot be deleted.'),
        );

        $client->delete();
    }
}