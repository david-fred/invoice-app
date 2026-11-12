<div class="mx-auto max-w-4xl space-y-6 p-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Invoices</h1>
        <a href="{{ route('invoices.create') }}" class="underline">New invoice</a>
    </div>

    <div class="flex flex-wrap gap-3">
        <input class="w-full rounded border-gray-300 sm:w-72" placeholder="Search number or client"
               wire:model.live.debounce.300ms="search">
        <select class="rounded border-gray-300" wire:model.live="status">
            <option value="">All statuses</option>
            <option value="draft">Draft</option>
            <option value="sent">Sent</option>
            <option value="paid">Paid</option>
            <option value="overdue">Overdue</option>
            <option value="cancelled">Cancelled</option>
        </select>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-4 py-2 font-medium">Number</th>
                    <th class="px-4 py-2 font-medium">Client</th>
                    <th class="px-4 py-2 font-medium">Due</th>
                    <th class="px-4 py-2 text-right font-medium">Total</th>
                    <th class="px-4 py-2 font-medium">Status</th>
                    <th class="px-4 py-2"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($this->invoices as $invoice)
                    <tr wire:key="invoice-{{ $invoice->id }}">
                        <td class="px-4 py-3">
                            <a href="{{ route('invoices.show', $invoice) }}" class="underline">{{ $invoice->number }}</a>
                        </td>
                        <td class="px-4 py-3">{{ $invoice->client->name }}</td>
                        <td class="whitespace-nowrap px-4 py-3">{{ $invoice->due_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-right tabular-nums">{{ number_format($invoice->total, 2) }}</td>
                        <td class="px-4 py-3">
                            @if ($invoice->isOverdue())
                                <span class="font-medium text-red-600">Overdue</span>
                            @else
                                {{ $invoice->status->label() }}
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @can('delete', $invoice)
                                <button type="button" class="text-red-600 hover:underline"
                                        wire:click="delete({{ $invoice->id }})"
                                        wire:confirm="Delete this draft?">Delete</button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">No invoices match your filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $this->invoices->links() }}
</div>