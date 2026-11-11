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
    public function handle(Invoice $invoice, array $data): Payment
    {
        throw_unless(
            $invoice->status === InvoiceStatus::Sent,
            new DomainException('Payments can only be recorded on sent invoices.'),
        );

        if ((float) $data['amount'] > $invoice->balance()) {
            throw ValidationException::withMessages([
                'amount' => 'The payment is larger than the outstanding balance.',
            ]);
        }

        return DB::transaction(function () use ($invoice, $data) {
            $payment = $invoice->payments()->create([
                'amount' => $data['amount'],
                'paid_on' => $data['paid_on'],
                'method' => $data['method'] ?? null,
                'reference' => $data['reference'] ?? null,
            ]);

            $amountPaid = round((float) $invoice->payments()->sum('amount'), 2);
            $isPaidInFull = $amountPaid >= round((float) $invoice->total, 2);

            $invoice->update([
                'amount_paid' => $amountPaid,
                'status' => $isPaidInFull ? InvoiceStatus::Paid : InvoiceStatus::Sent,
                'paid_at' => $isPaidInFull ? now() : null,
            ]);

            return $payment;
        });
    }
}