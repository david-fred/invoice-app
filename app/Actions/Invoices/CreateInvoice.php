<?php

namespace App\Actions\Invoices;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CreateInvoice
{
    public function __construct(
        private SyncInvoiceItems $syncItems,
        private RefreshInvoiceTotals $refreshTotals,
    ) {}

    public function handle(User $user, array $data): Invoice
    {
        return DB::transaction(function () use ($user, $data) {
            $sequence = (int) $user->invoices()->max('sequence') + 1;

            $invoice = $user->invoices()->create([
                ...Arr::except($data, ['items']),
                'sequence' => $sequence,
                'number' => sprintf('INV-%04d', $sequence),
                'status' => InvoiceStatus::Draft,
            ]);

            $this->syncItems->handle($invoice, $data['items']);
            $this->refreshTotals->handle($invoice);

            return $invoice;
        });
    }
}
