<?php

namespace App\Livewire\Invoices;

use App\Actions\Invoices\DeleteInvoice;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\CurrencyTotals;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    /** Columns the user can sort by, mapped to the database column they use. */
    private const SORTABLE = [
        'number' => 'sequence',
        'client' => 'client',
        'due' => 'due_date',
        'total' => 'total',
        'status' => 'status',
    ];

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $currency = '';

    /** Issue date range, inclusive. */
    #[Url(except: '')]
    public string $issuedFrom = '';

    #[Url(except: '')]
    public string $issuedTo = '';

    /** Payments received in this month (YYYY-MM). Takes priority over the custom range below. */
    #[Url(except: '')]
    public string $paidMonth = '';

    /** Custom payment date range, inclusive. */
    #[Url(except: '')]
    public string $paidFrom = '';

    #[Url(except: '')]
    public string $paidTo = '';

    /** Empty means the default order: newest created first. */
    #[Url(except: '')]
    public string $sort = '';

    #[Url(except: 'desc')]
    public string $direction = 'desc';

    /** Any filter change goes back to page 1. */
    public function updated(string $property): void
    {
        if ($property !== 'page') {
            $this->resetPage();
        }
    }

    public function sortBy(string $column): void
    {
        if (! array_key_exists($column, self::SORTABLE)) {
            return;
        }

        if ($this->sort === $column) {
            $this->direction = $this->direction === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $column;
            $this->direction = 'asc';
        }

        $this->resetPage();
    }

    public function resetSort(): void
    {
        $this->sort = '';
        $this->direction = 'desc';
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'status', 'currency', 'issuedFrom', 'issuedTo', 'paidMonth', 'paidFrom', 'paidTo');
        $this->resetPage();
    }

    public function sortIndicator(string $column): string
    {
        if ($this->sort !== $column) {
            return '';
        }

        return $this->direction === 'asc' ? '▲' : '▼';
    }

    /** Currencies that appear on this user's invoices, for the currency filter. */
    #[Computed]
    public function currencies(): array
    {
        return Invoice::query()
            ->where('user_id', Auth::id())
            ->distinct()
            ->orderBy('currency')
            ->pluck('currency')
            ->all();
    }

    #[Computed]
    public function invoices()
    {
        return $this->applySort($this->filteredInvoices()->with('client'))->paginate(10);
    }

    /**
     * Totals for every matching invoice, one row per currency.
     * Ignores sorting and paging, so the figures match the whole filtered set.
     */
    #[Computed]
    public function summary(): array
    {
        $invoices = $this->filteredInvoices()->get();
        $byCurrency = fn (Invoice $invoice) => $invoice->currency;

        return [
            'count' => $invoices->countBy('currency')->sortKeys()->all(),
            'total' => CurrencyTotals::sum($invoices, fn (Invoice $invoice) => $invoice->total, $byCurrency),
            'paid' => CurrencyTotals::sum($invoices, fn (Invoice $invoice) => $invoice->amount_paid, $byCurrency),
            'balance' => CurrencyTotals::sum($invoices, fn (Invoice $invoice) => $invoice->balance(), $byCurrency),
            'received' => $this->receivedInPeriod(),
        ];
    }

    /** Every invoice that matches the filters. Sorting and paging are added on top. */
    private function filteredInvoices(): Builder
    {
        $query = Invoice::query()
            ->where('user_id', Auth::id())
            ->search($this->search)
            ->withStatus($this->status);

        if ($this->currency !== '') {
            $query->where('currency', $this->currency);
        }

        if ($this->issuedFrom !== '') {
            $query->whereDate('issue_date', '>=', $this->issuedFrom);
        }

        if ($this->issuedTo !== '') {
            $query->whereDate('issue_date', '<=', $this->issuedTo);
        }

        // "Paid" filter: invoices that have at least one payment in the period.
        if ($range = $this->paymentRange()) {
            $query->whereHas('payments', fn (Builder $payments) => $payments->whereBetween('paid_on', $range));
        }

        return $query;
    }

    /**
     * The payment date window as [from, to], or null when no payment filter is set.
     *
     * @return array{0: string, 1: string}|null
     */
    private function paymentRange(): ?array
    {
        if (preg_match('/^(\d{4})-(\d{2})$/', $this->paidMonth, $matches) === 1) {
            $start = Carbon::createFromDate((int) $matches[1], (int) $matches[2], 1)->startOfMonth();

            return [$start->toDateString(), $start->copy()->endOfMonth()->toDateString()];
        }

        if ($this->paidFrom === '' && $this->paidTo === '') {
            return null;
        }

        return [
            $this->paidFrom !== '' ? $this->paidFrom : '0001-01-01',
            $this->paidTo !== '' ? $this->paidTo : '9999-12-31',
        ];
    }

    /** Payments received in the chosen period, per currency. Null when no payment period is set. */
    private function receivedInPeriod(): ?array
    {
        $range = $this->paymentRange();

        if ($range === null) {
            return null;
        }

        $payments = Payment::query()
            ->with('invoice')
            ->whereIn('invoice_id', $this->filteredInvoices()->select('invoices.id'))
            ->whereBetween('paid_on', $range)
            ->get();

        return CurrencyTotals::sum(
            $payments,
            fn (Payment $payment) => $payment->amount,
            fn (Payment $payment) => $payment->invoice->currency,
        );
    }

    private function applySort(Builder $query): Builder
    {
        if (! array_key_exists($this->sort, self::SORTABLE)) {
            return $query->latest('created_at')->latest('id');
        }

        // Only 'asc' or 'desc' ever reach the database, whatever the URL contains.
        $direction = $this->direction === 'asc' ? 'asc' : 'desc';

        if ($this->sort === 'client') {
            return $query->orderBy(
                DB::table('clients')
                    ->select('name')
                    ->whereColumn('clients.id', 'invoices.client_id')
                    ->limit(1),
                $direction,
            );
        }

        // Totals are only comparable within one currency, so group by currency first.
        if ($this->sort === 'total') {
            return $query->orderBy('currency', $direction)->orderBy('total', $direction);
        }

        return $query->orderBy(self::SORTABLE[$this->sort], $direction);
    }

    public function delete(int $invoiceId, DeleteInvoice $action): void
    {
        $invoice = Auth::user()->invoices()->findOrFail($invoiceId);
        $this->authorize('delete', $invoice);

        $action->handle($invoice);
    }

    public function render()
    {
        return view('livewire.invoices.index');
    }
}
