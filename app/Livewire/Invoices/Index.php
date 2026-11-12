<?php

namespace App\Livewire\Invoices;

use App\Actions\Invoices\DeleteInvoice;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Builder;
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

    /** Empty means the default order: newest created first. */
    #[Url(except: '')]
    public string $sort = '';

    #[Url(except: 'desc')]
    public string $direction = 'desc';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
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

    public function sortIndicator(string $column): string
    {
        if ($this->sort !== $column) {
            return '';
        }

        return $this->direction === 'asc' ? '▲' : '▼';
    }

    #[Computed]
    public function invoices()
    {
        $query = Invoice::query()
            ->where('user_id', Auth::id())
            ->with('client')
            ->search($this->search)
            ->withStatus($this->status);

        return $this->applySort($query)->paginate(10);
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
