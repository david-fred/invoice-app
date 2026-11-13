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
        return DB::transaction(function () use ($invoice, $data): Invoice {
            // Lock the row so a send in another tab can't land between the check and the write.
            /** @var Invoice $locked */
            $locked = Invoice::query()->whereKey($invoice->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== InvoiceStatus::Draft) {
                throw new DomainException('Sent invoices cannot be edited.');
            }

            $locked->update(Arr::except($data, ['items']));
            $this->syncItems->handle($locked, $data['items']);
            $this->refreshTotals->handle($locked);

            return $locked;
        });
    }
}
