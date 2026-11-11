<?php

namespace App\Actions\Invoices;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Invoice;

class GenerateInvoicePdf
{
    public function handle(Invoice $invoice): \Barryvdh\DomPDF\PDF
    {
        $invoice->loadMissing(['client', 'items']);

        return Pdf::loadView('invoices.pdf', compact('invoice'));
    }
}