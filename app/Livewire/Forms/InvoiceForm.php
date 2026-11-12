<?php

namespace App\Livewire\Forms;

use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Form;

class InvoiceForm extends Form
{
    public string $client_id = '';

    public string $issue_date = '';

    public string $due_date = '';

    public string $currency = 'USD';

    public string $discount = '0';

    public string $tax_rate = '0';

    public string $notes = '';

    /** @var array<int, array{description: string, quantity: string, unit_price: string}> */
    public array $items = [];

    public function rules(): array
    {
        return [
            'client_id' => ['required', Rule::exists('clients', 'id')->where('user_id', Auth::id())],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'currency' => ['required', 'string', 'size:3'],
            'discount' => ['required', 'numeric', 'min:0'],
            'tax_rate' => ['required', 'numeric', 'between:0,100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function setInvoice(Invoice $invoice): void
    {
        $this->client_id = (string) $invoice->client_id;
        $this->issue_date = $invoice->issue_date->toDateString();
        $this->due_date = $invoice->due_date->toDateString();
        $this->currency = $invoice->currency;
        $this->discount = (string) $invoice->discount;
        $this->tax_rate = (string) $invoice->tax_rate;
        $this->notes = $invoice->notes ?? '';
        $this->items = $invoice->items->map(fn ($item) => [
            'description' => $item->description,
            'quantity' => (string) $item->quantity,
            'unit_price' => (string) $item->unit_price,
        ])->values()->all();
    }
}
