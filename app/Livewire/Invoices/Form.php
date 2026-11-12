<?php

namespace App\Livewire\Invoices;

use App\Actions\Invoices\CreateInvoice;
use App\Actions\Invoices\UpdateInvoice;
use App\Livewire\Forms\InvoiceForm;
use App\Models\Invoice;
use App\Services\InvoiceCalculator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Form extends Component
{
    public InvoiceForm $form;

    public ?Invoice $invoice = null;

    public function mount(?Invoice $invoice = null): void
    {
        if ($invoice) {
            $this->authorize('update', $invoice);
            $this->invoice = $invoice;
            $this->form->setInvoice($invoice);

            return;
        }

        $this->form->issue_date = now()->toDateString();
        $this->form->due_date = now()->addDays(14)->toDateString();
        $this->form->items = [$this->blankItem()];
    }

    #[Computed]
    public function clients()
    {
        return Auth::user()->clients()->orderBy('name')->get();
    }

    #[Computed]
    public function totals(): array
    {
        return InvoiceCalculator::totals(
            $this->form->items,
            $this->form->discount,
            $this->form->tax_rate,
        );
    }

    public function lineAmount(int $index): float
    {
        $item = $this->form->items[$index];

        return InvoiceCalculator::lineAmount($item['quantity'], $item['unit_price']);
    }

    public function addItem(): void
    {
        $this->form->items[] = $this->blankItem();
    }

    public function removeItem(int $index): void
    {
        unset($this->form->items[$index]);
        $this->form->items = array_values($this->form->items);
    }

    public function save(CreateInvoice $create, UpdateInvoice $update): void
    {
        $this->form->validate();

        $invoice = $this->invoice
            ? $update->handle($this->invoice, $this->form->all())
            : $create->handle(Auth::user(), $this->form->all());

        $this->redirectRoute('invoices.show', $invoice);
    }

    private function blankItem(): array
    {
        return ['description' => '', 'quantity' => '1', 'unit_price' => ''];
    }

    public function render()
    {
        return view('livewire.invoices.form');
    }
}
