<?php

namespace App\Livewire;

use App\Enums\InvoiceStatus;
use App\Models\Payment;
use App\Services\CurrencyTotals;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Dashboard extends Component
{
    /** Money still owed on sent invoices, one total per currency. */
    #[Computed]
    public function outstanding(): array
    {
        $invoices = Auth::user()->invoices()
            ->where('status', InvoiceStatus::Sent)
            ->get();

        return CurrencyTotals::sum(
            $invoices,
            fn ($invoice) => $invoice->balance(),
            fn ($invoice) => $invoice->currency,
        );
    }

    /** Money owed on overdue invoices, one total per currency. */
    #[Computed]
    public function overdueTotals(): array
    {
        $invoices = Auth::user()->invoices()->overdue()->get();

        return CurrencyTotals::sum(
            $invoices,
            fn ($invoice) => $invoice->balance(),
            fn ($invoice) => $invoice->currency,
        );
    }

    /** Number of overdue invoices. */
    #[Computed]
    public function overdueCount(): int
    {
        return Auth::user()->invoices()->overdue()->count();
    }

    /** Payments received this calendar month, one total per invoice currency. */
    #[Computed]
    public function paidThisMonth(): array
    {
        $payments = Payment::query()
            ->with('invoice')
            ->whereHas('invoice', fn ($query) => $query->where('user_id', Auth::id()))
            ->whereBetween('paid_on', [
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString(),
            ])
            ->get();

        return CurrencyTotals::sum(
            $payments,
            fn ($payment) => $payment->amount,
            fn ($payment) => $payment->invoice->currency,
        );
    }

    /** Invoices that became fully paid this calendar month. */
    #[Computed]
    public function invoicesPaidThisMonth(): int
    {
        return Auth::user()->invoices()
            ->where('status', InvoiceStatus::Paid)
            ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();
    }

    #[Computed]
    public function recentInvoices()
    {
        return Auth::user()->invoices()
            ->with('client')
            ->latest('issue_date')
            ->take(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
