<?php

namespace Database\Seeders;

use App\Actions\Invoices\CreateInvoice;
use App\Actions\Invoices\SendInvoice;
use App\Actions\Payments\RecordPayment;
use App\Models\User;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();

        if (! $user) {
            $this->command->warn('No users found. Register an account first.');

            return;
        }

        if ($user->invoices()->exists()) {
            $this->command->warn('Invoices already exist for this user. Skipping.');

            return;
        }

        $clients = $user->clients()->get();

        if ($clients->isEmpty()) {
            $this->command->warn('No clients found. Run ClientSeeder first.');

            return;
        }

        // issued: days ago, due: days from today (negative = already past),
        // stage: draft | sent | partial | paid
        $samples = [
            ['issued' => 0,  'due' => 14,  'stage' => 'draft',   'tax' => '0',   'discount' => '0',
                'items' => [['Logo design', '1', '250'], ['Brand guidelines', '1', '400']]],
            ['issued' => 2,  'due' => 12,  'stage' => 'draft',   'tax' => '10',  'discount' => '0',
                'items' => [['Hosting (monthly)', '3', '20']]],
            ['issued' => 5,  'due' => 9,   'stage' => 'sent',    'tax' => '10',  'discount' => '20',
                'items' => [['Website development', '10', '85'], ['Domain setup', '1', '15']]],
            ['issued' => 10, 'due' => 4,   'stage' => 'partial', 'tax' => '7.5', 'discount' => '0',
                'items' => [['Consulting hours', '6', '120']]],
            ['issued' => 20, 'due' => -6,  'stage' => 'paid',    'tax' => '0',   'discount' => '0',
                'items' => [['Monthly retainer', '1', '1200']]],
            ['issued' => 40, 'due' => -26, 'stage' => 'sent',    'tax' => '10',  'discount' => '0',
                'items' => [['SEO audit', '1', '600'], ['Keyword report', '2', '150']]],
            ['issued' => 60, 'due' => -46, 'stage' => 'partial', 'tax' => '0',   'discount' => '50',
                'items' => [['Mobile app prototype', '4', '375']]],
            ['issued' => 3,  'due' => 11,  'stage' => 'sent',    'tax' => '5',   'discount' => '0',
                'items' => [['Photography session', '1', '480'], ['Editing (per photo)', '25', '12']]],
        ];

        $create = app(CreateInvoice::class);
        $send = app(SendInvoice::class);
        $pay = app(RecordPayment::class);

        foreach ($samples as $index => $sample) {
            $client = $clients[$index % $clients->count()];

            $invoice = $create->handle($user, [
                'client_id' => $client->id,
                'issue_date' => now()->subDays($sample['issued'])->toDateString(),
                'due_date' => now()->addDays($sample['due'])->toDateString(),
                'currency' => 'USD',
                'notes' => '',
                'discount' => $sample['discount'],
                'tax_rate' => $sample['tax'],
                'items' => array_map(fn (array $item) => [
                    'description' => $item[0],
                    'quantity' => $item[1],
                    'unit_price' => $item[2],
                ], $sample['items']),
            ]);

            if ($sample['stage'] !== 'draft') {
                $send->handle($invoice);
            }

            $total = (float) $invoice->total;

            $amount = match ($sample['stage']) {
                'partial' => round($total / 2, 2),
                'paid' => $total,
                default => null,
            };

            if ($amount !== null) {
                $pay->handle($invoice, [
                    'amount' => number_format($amount, 2, '.', ''),
                    'paid_on' => now()->subDay()->toDateString(),
                    'method' => 'Bank transfer',
                ]);
            }
        }

        $this->command->info('Created '.count($samples).' invoices.');
    }
}
