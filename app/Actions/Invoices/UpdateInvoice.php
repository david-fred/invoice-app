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

    public function handle(Invoice $invoice, array $data): Invoice
    {
        throw_unless(
            $invoice->status === InvoiceStatus::Draft,
            new DomainException('Sent invoices cannot be edited.'),
        );

        return DB::transaction(function () use ($invoice, $data) {
            $invoice->update(Arr::except($data, ['items']));
            $this->syncItems->handle($invoice, $data['items']);
            $this->refreshTotals->handle($invoice);

            return $invoice;
        });
    }
}