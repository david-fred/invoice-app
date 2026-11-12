<?php

namespace App\Actions\Invoices;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use DomainException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UpdateInvoice
{
    public function __construct(
        private SyncInvoiceItems $syncItems,
        private RefreshInvoiceTotals $refreshTotals,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Invoice $invoice, array $data): Invoice
    {
        if ($invoice->status !== InvoiceStatus::Draft) {
            throw new DomainException('Sent invoices cannot be edited.');
        }

        return DB::transaction(function () use ($invoice, $data): Invoice {
            $invoice->update(Arr::except($data, ['items']));
            $this->syncItems->handle($invoice, $data['items']);
            $this->refreshTotals->handle($invoice);

            return $invoice;
        });
    }
}
