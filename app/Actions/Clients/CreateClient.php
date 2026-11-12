<?php

namespace App\Actions\Clients;

use App\Models\Client;
use App\Models\User;

class CreateClient
{
    public function handle(User $user, array $data): Client
    {
        return $user->clients()->create($data);
    }
}
