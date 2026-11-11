<?php

namespace App\Livewire\Invoices;

use App\Actions\Invoices\DeleteInvoice;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $status = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function invoices()
    {
        return Auth::user()->invoices()
            ->with('client')
            ->search($this->search)
            ->withStatus($this->status)
            ->latest('issue_date')
            ->paginate(10);
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