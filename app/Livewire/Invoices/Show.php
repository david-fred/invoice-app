<?php

namespace App\Livewire\Invoices;

use App\Actions\Invoices\DeleteInvoice;
use App\Actions\Invoices\SendInvoice;
use App\Actions\Payments\RecordPayment;
use App\Models\Invoice;
use Livewire\Component;

class Show extends Component
{
    public Invoice $invoice;

    public string $amount = '';

    public string $paid_on = '';

    public string $method = '';

    public function mount(Invoice $invoice): void
    {
        $this->authorize('view', $invoice);

        $this->invoice = $invoice;
        $this->paid_on = now()->toDateString();
    }

    public function send(SendInvoice $action): void
    {
        $this->authorize('send', $this->invoice);

        $action->handle($this->invoice);
        $this->invoice->refresh();
    }

    public function recordPayment(RecordPayment $action): void
    {
        $this->authorize('recordPayment', $this->invoice);

        $data = $this->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'paid_on' => ['required', 'date'],
            'method' => ['nullable', 'string', 'max:50'],
        ]);

        $action->handle($this->invoice, $data);

        $this->reset('amount', 'method');
        $this->invoice->refresh();
    }

    public function delete(DeleteInvoice $action): void
    {
        $this->authorize('delete', $this->invoice);

        $action->handle($this->invoice);

        $this->redirectRoute('invoices.index');
    }

    public function render()
    {
        return view('livewire.invoices.show');
    }
}
