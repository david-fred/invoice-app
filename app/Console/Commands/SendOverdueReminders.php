<?php

namespace App\Console\Commands;

use App\Actions\Invoices\SendOverdueReminder;
use App\Models\Invoice;
use Illuminate\Console\Command;

class SendOverdueReminders extends Command
{
    protected $signature = 'invoices:remind-overdue';

    protected $description = 'Queue reminder emails for overdue invoices, at most once a week per invoice.';

    public function handle(SendOverdueReminder $remind): int
    {
        $invoices = Invoice::query()->dueForReminder()->with('client')->get();

        foreach ($invoices as $invoice) {
            $remind->handle($invoice);
        }

        $this->info("Queued {$invoices->count()} reminder(s).");

        return self::SUCCESS;
    }
}
