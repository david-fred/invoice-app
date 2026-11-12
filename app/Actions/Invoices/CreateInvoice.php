<?php

namespace App\Actions\Invoices;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateInvoice
{
    private const MAX_ATTEMPTS = 3;

    public function __construct(
        private SyncInvoiceItems $syncItems,
        private RefreshInvoiceTotals $refreshTotals,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(User $user, array $data): Invoice
    {
        return retry(
            self::MAX_ATTEMPTS,
            fn () => $this->create($user, $data),
            0,
            fn (Throwable $e) => $e instanceof UniqueConstraintViolationException,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function create(User $user, array $data): Invoice
    {
        return DB::transaction(function () use ($user, $data): Invoice {
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
