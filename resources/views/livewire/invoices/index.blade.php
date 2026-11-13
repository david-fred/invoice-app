<div class="mx-auto max-w-5xl space-y-6 p-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Invoices</h1>
        <a href="{{ route('invoices.create') }}" class="underline">New invoice</a>
    </div>

    {{-- Filters --}}
    <div class="space-y-3">
        <div class="flex flex-wrap items-end gap-3">
            <label class="text-sm">
                <span class="block text-gray-600">Search</span>
                <input class="rounded" placeholder="Number or client" wire:model.live.debounce.300ms="search">
            </label>

            <label class="text-sm">
                <span class="block text-gray-600">Currency</span>
                <select class="rounded" wire:model.live="currency">
                    <option value="">All currencies</option>
                    @foreach ($this->currencies as $code)
                        <option value="{{ $code }}">{{ $code }}</option>
                    @endforeach
                </select>
            </label>

            <label class="text-sm">
                <span class="block text-gray-600">Status</span>
                <select class="rounded" wire:model.live="status">
                    <option value="">All statuses</option>
                    <option value="draft">Draft</option>
                    <option value="sent">Sent (incl. overdue)</option>
                    <option value="paid">Paid</option>
                    <option value="overdue">Overdue</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </label>
        </div>

        <div class="flex flex-wrap items-end gap-3">
            <label class="text-sm">
                <span class="block text-gray-600">Issued from</span>
                <input type="date" class="rounded" wire:model.live="issuedFrom">
            </label>

            <label class="text-sm">
                <span class="block text-gray-600">Issued to</span>
                <input type="date" class="rounded" wire:model.live="issuedTo">
            </label>

            <label class="text-sm">
                <span class="block text-gray-600">Paid in month</span>
                <input type="month" class="rounded" wire:model.live="paidMonth">
            </label>

            <label class="text-sm">
                <span class="block text-gray-600">Paid from</span>
                <input type="date" class="rounded" wire:model.live="paidFrom">
            </label>

            <label class="text-sm">
                <span class="block text-gray-600">Paid to</span>
                <input type="date" class="rounded" wire:model.live="paidTo">
            </label>

            <button type="button" class="pb-1 text-sm underline" wire:click="clearFilters">Clear filters</button>
        </div>
    </div>

    {{-- Totals for every matching invoice, one row per currency --}}
    @php($summary = $this->summary)
    @if (count($summary['total']) > 0)
        <div class="space-y-2">
            <p class="text-sm text-gray-600">Totals for all matching invoices, one row per currency. Paging is ignored.</p>
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b">
                        <th class="py-2 pr-6">Currency</th>
                        <th class="py-2 pr-6 text-right">Invoices</th>
                        <th class="py-2 pr-6 text-right">Total</th>
                        <th class="py-2 pr-6 text-right">Paid</th>
                        <th class="py-2 pr-6 text-right">Balance</th>
                        @if ($summary['received'] !== null)
                            <th class="py-2 text-right">Received in period</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($summary['total'] as $code => $total)
                        <tr class="border-b">
                            <td class="py-2 pr-6">{{ $code }}</td>
                            <td class="py-2 pr-6 text-right tabular-nums">{{ $summary['count'][$code] }}</td>
                            <td class="py-2 pr-6 text-right tabular-nums">{{ number_format($total, 2) }}</td>
                            <td class="py-2 pr-6 text-right tabular-nums">{{ number_format($summary['paid'][$code], 2) }}</td>
                            <td class="py-2 pr-6 text-right tabular-nums">{{ number_format($summary['balance'][$code], 2) }}</td>
                            @if ($summary['received'] !== null)
                                <td class="py-2 text-right tabular-nums">{{ number_format($summary['received'][$code] ?? 0, 2) }}</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Invoice list --}}
    <table class="w-full text-left">
        <thead>
            <tr class="border-b">
                <th class="py-2 pr-6">
                    <button type="button" class="font-medium hover:underline" wire:click="sortBy('number')">
                        Number <span class="text-xs">{{ $this->sortIndicator('number') }}</span>
                    </button>
                </th>
                <th class="py-2 pr-6">
                    <button type="button" class="font-medium hover:underline" wire:click="sortBy('client')">
                        Client <span class="text-xs">{{ $this->sortIndicator('client') }}</span>
                    </button>
                </th>
                <th class="py-2 pr-6">
                    <button type="button" class="font-medium hover:underline" wire:click="sortBy('due')">
                        Due <span class="text-xs">{{ $this->sortIndicator('due') }}</span>
                    </button>
                </th>
                <th class="py-2 pr-6 text-right">
                    <button type="button" class="font-medium hover:underline" wire:click="sortBy('total')">
                        Total <span class="text-xs">{{ $this->sortIndicator('total') }}</span>
                    </button>
                </th>
                <th class="py-2 pr-6 text-right">Paid</th>
                <th class="py-2 pr-6 text-right">Balance</th>
                <th class="py-2 pr-6">
                    <button type="button" class="font-medium hover:underline" wire:click="sortBy('status')">
                        Status <span class="text-xs">{{ $this->sortIndicator('status') }}</span>
                    </button>
                </th>
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
                    <td class="whitespace-nowrap py-2 pr-6 text-right tabular-nums">
                        {{ $invoice->currency }} {{ number_format($invoice->total, 2) }}
                    </td>
                    <td class="whitespace-nowrap py-2 pr-6 text-right tabular-nums">
                        {{ $invoice->currency }} {{ number_format($invoice->amount_paid, 2) }}
                    </td>
                    <td class="whitespace-nowrap py-2 pr-6 text-right tabular-nums">
                        {{ $invoice->currency }} {{ number_format($invoice->balance(), 2) }}
                    </td>
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
                    <td colspan="8" class="py-4 text-gray-500">No invoices match your filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $this->invoices->links() }}
</div>
