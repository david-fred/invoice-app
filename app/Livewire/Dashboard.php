<?php

namespace App\Livewire;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Dashboard extends Component
{
    /** Money still owed on sent invoices. */
    #[Computed]
    public function outstanding(): float
    {
        return (float) Auth::user()->invoices()
            ->where('status', InvoiceStatus::Sent)
            ->get()
            ->sum(fn (Invoice $invoice) => $invoice->balance());
    }

    /** Sent invoices that are past due and not fully paid. */
    #[Computed]
    public function overdueCount(): int
    {
        return Auth::user()->invoices()->overdue()->count();
    }

    /** Payments received between the first and last day of this month. */
    #[Computed]
    public function paidThisMonth(): float
    {
        return (float) Payment::query()
            ->whereHas('invoice', fn ($query) => $query->where('user_id', Auth::id()))
            ->whereBetween('paid_on', [
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString(),
            ])
            ->sum('amount');
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
