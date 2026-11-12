<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sequence = fake()->unique()->numberBetween(1, 9999);

        return [
            'user_id' => User::factory(),
            'client_id' => Client::factory(),
            'sequence' => $sequence,
            'number' => sprintf('INV-%04d', $sequence),
            'status' => InvoiceStatus::Draft,
            'issue_date' => now(),
            'due_date' => now()->addDays(14),
            'currency' => 'USD',
        ];
    }

    public function sent(): static
    {
        return $this->state(fn () => [
            'status' => InvoiceStatus::Sent,
            'sent_at' => now(),
        ]);
    }
}
