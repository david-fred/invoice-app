<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'amount' => fake()->randomFloat(2, 10, 200),
            'paid_on' => now(),
            'method' => fake()->randomElement(['bank transfer', 'cash', 'card']),
            'reference' => fake()->bothify('REF-####'),
        ];
    }
}
