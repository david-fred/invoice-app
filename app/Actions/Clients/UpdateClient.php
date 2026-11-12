<?php

namespace App\Actions\Clients;

use App\Models\Client;

class UpdateClient
{
    public function handle(Client $client, array $data): Client
    {
        $client->update($data);

        return $client;
    }
}
