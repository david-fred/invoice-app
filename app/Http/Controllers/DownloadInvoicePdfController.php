<?php

namespace App\Http\Controllers;

use App\Actions\Invoices\GenerateInvoicePdf;
use App\Models\Invoice;
use Illuminate\Support\Facades\Gate;

class DownloadInvoicePdfController extends Controller
{
    public function __invoke(Invoice $invoice, GenerateInvoicePdf $generatePdf)
    {
        Gate::authorize('view', $invoice);

        return $generatePdf->handle($invoice)->download("{$invoice->number}.pdf");
    }
}
