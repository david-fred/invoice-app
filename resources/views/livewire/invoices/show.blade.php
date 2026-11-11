<div>
    {{-- Care about people's approval and you will be their prisoner. --}}
</div>
<div class="mx-auto max-w-4xl space-y-6 p-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">{{ $invoice->number }}</h1>
        <div class="flex gap-3">
            @can('update', $invoice)
                <a href="{{ route('invoices.edit', $invoice) }}">Edit</a>
            @endcan
            @can('send', $invoice)
                <button wire:click="send" wire:confirm="Send this invoice? It will be locked.">Send</button>
            @endcan
            <a href="{{ route('invoices.pdf', $invoice) }}">Download PDF</a>
            @can('delete', $invoice)
                <button wire:click="delete" wire:confirm="Delete this draft?">Delete</button>
            @endcan
        </div>
    </div>

    <p>Status: {{ $invoice->isOverdue() ? 'Overdue' : $invoice->status->label() }}</p>
    <p>Total {{ number_format($invoice->total, 2) }} · Paid {{ number_format($invoice->amount_paid, 2) }} · Balance {{ number_format($invoice->balance(), 2) }}</p>

    {{-- Loop $invoice->items for line items and $invoice->payments for payment history --}}

    @can('recordPayment', $invoice)
        <form wire:submit="recordPayment" class="flex gap-2">
            <input type="number" step="0.01" wire:model="amount" placeholder="Amount" class="rounded">
            <input type="date" wire:model="paid_on" class="rounded">
            <input wire:model="method" placeholder="Method" class="rounded">
            <button type="submit" class="rounded bg-gray-900 px-4 py-2 text-white">Record payment</button>
        </form>
    @endcan
</div>