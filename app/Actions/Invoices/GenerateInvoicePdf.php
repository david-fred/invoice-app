<?php

namespace App\Actions\Invoices;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerateInvoicePdf
{
    public function handle(Invoice $invoice): \Barryvdh\DomPDF\PDF
    {
        $invoice->loadMissing(['client', 'items']);

        return Pdf::loadView('invoices.pdf', compact('invoice'));
    }
}
