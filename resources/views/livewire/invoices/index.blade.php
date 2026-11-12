<div class="mx-auto max-w-4xl space-y-6 p-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Invoices</h1>
        <a href="{{ route('invoices.create') }}" class="underline">New invoice</a>
    </div>

    <div class="flex flex-wrap gap-3">
        <input class="rounded" placeholder="Search number or client"
               wire:model.live.debounce.300ms="search">
        <select class="rounded" wire:model.live="status">
            <option value="">All statuses</option>
            <option value="draft">Draft</option>
            <option value="sent">Sent</option>
            <option value="paid">Paid</option>
            <option value="overdue">Overdue</option>
            <option value="cancelled">Cancelled</option>
        </select>
    </div>

    <table class="w-full text-left">
        <thead>
            <tr class="border-b">
                <th class="py-2 pr-6">Number</th>
                <th class="py-2 pr-6">Client</th>
                <th class="py-2 pr-6">Due</th>
                <th class="py-2 pr-6 text-right">Total</th>
                <th class="py-2 pr-6">Status</th>
                <th class="py-2"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($this->invoices as $invoice)
                <tr wire:key="invoice-{{ $invoice->id }}" class="border-b">
                    <td class="py-2 pr-6">
                        <a href="{{ route('invoices.show', $invoice) }}" class="underline">{{ $invoice->number }}</a>
                    </td>
                    <td class="py-2 pr-6">{{ $invoice->client->name }}</td>
                    <td class="whitespace-nowrap py-2 pr-6">{{ $invoice->due_date->format('d M Y') }}</td>
                    <td class="whitespace-nowrap py-2 pr-6 text-right tabular-nums">{{ number_format($invoice->total, 2) }}</td>
                    <td class="whitespace-nowrap py-2 pr-6">
                        @if ($invoice->isOverdue())
                            <span class="text-red-600">Overdue</span>
                        @else
                            {{ $invoice->status->label() }}
                        @endif
                    </td>
                    <td class="py-2 text-right">
                        @can('delete', $invoice)
                            <button type="button" class="text-red-600 hover:underline"
                                    wire:click="delete({{ $invoice->id }})"
                                    wire:confirm="Delete this draft?">Delete</button>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-4 text-gray-500">No invoices match your filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $this->invoices->links() }}
</div>
