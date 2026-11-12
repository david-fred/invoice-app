<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();

        if (! $user) {
            $this->command->warn('No users found. Register an account first.');

            return;
        }

        Client::factory()
            ->count(5)
            ->for($user)
            ->create();
    }
}
