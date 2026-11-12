<div class="mx-auto max-w-5xl space-y-4 p-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Invoices</h1>
        <a href="{{ route('invoices.create') }}" class="underline">New invoice</a>
    </div>

    <div class="flex gap-2">
        <input class="rounded" placeholder="Search number or client"
               wire:model.live.debounce.300ms="search">
        <select class="rounded" wire:model.live="status">
            <option value="">All</option>
            <option value="draft">Draft</option>
            <option value="sent">Sent</option>
            <option value="paid">Paid</option>
                        <option value="cancelled">Cancelled</option>
            <option value="overdue">Overdue</option>
        </select>
    </div>

    <table class="w-full text-left">
        @foreach ($this->invoices as $invoice)
            <tr wire:key="invoice-{{ $invoice->id }}">
                <td><a href="{{ route('invoices.show', $invoice) }}">{{ $invoice->number }}</a></td>
                <td>{{ $invoice->client->name }}</td>
                <td>{{ $invoice->due_date->format('d M Y') }}</td>
                <td class="text-right">{{ number_format($invoice->total, 2) }}</td>
                <td>{{ $invoice->isOverdue() ? 'Overdue' : $invoice->status->label() }}</td>
                <td>
                    @can('delete', $invoice)
                        <button wire:click="delete({{ $invoice->id }})"
                                wire:confirm="Delete this draft?">Delete</button>
                    @endcan
                </td>
            </tr>
        @endforeach
    </table>

    {{ $this->invoices->links() }}
</div>