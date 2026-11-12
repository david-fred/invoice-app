<?php

namespace App\Actions\Payments;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Payment;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecordPayment
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Invoice $invoice, array $data): Payment
    {
        $attributes = [
            'amount' => $data['amount'],
            'paid_on' => $data['paid_on'],
            'method' => $data['method'] ?? null,
            'reference' => $data['reference'] ?? null,
        ];

        return DB::transaction(fn (): Payment => $this->record($invoice, $attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function record(Invoice $invoice, array $attributes): Payment
    {
        // Lock the row so two payments can't both pass the balance check.
        /** @var Invoice $locked */
        $locked = Invoice::query()->whereKey($invoice->id)->lockForUpdate()->firstOrFail();

        if ($locked->status !== InvoiceStatus::Sent) {
            throw new DomainException('Payments can only be recorded on sent invoices.');
        }

        if ((float) $attributes['amount'] > $locked->balance()) {
            throw ValidationException::withMessages([
                'amount' => 'The payment is larger than the outstanding balance.',
            ]);
        }

        $payment = $locked->payments()->create($attributes);

        $amountPaid = round((float) $locked->payments()->sum('amount'), 2);
        $isPaidInFull = $amountPaid >= round((float) $locked->total, 2);

        $locked->update([
            'amount_paid' => $amountPaid,
            'status' => $isPaidInFull ? InvoiceStatus::Paid : InvoiceStatus::Sent,
            'paid_at' => $isPaidInFull ? now() : null,
        ]);

        return $payment;
    }
}
