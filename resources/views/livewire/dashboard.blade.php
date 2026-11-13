<div class="mx-auto max-w-4xl space-y-6 p-6">
    <h1 class="text-2xl font-semibold">Dashboard</h1>

    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded border p-4">
            <p class="text-sm text-gray-600">Outstanding</p>
            @forelse ($this->outstanding as $currency => $amount)
                @if ($loop->first)
                    <p class="mt-1 text-xl font-semibold tabular-nums">{{ $currency }} {{ number_format($amount, 2) }}</p>
                @else
                    <p class="text-sm text-gray-600 tabular-nums">{{ $currency }} {{ number_format($amount, 2) }}</p>
                @endif
            @empty
                <p class="mt-1 text-xl font-semibold tabular-nums">0.00</p>
            @endforelse
        </div>

        <div class="rounded border p-4">
            <p class="text-sm text-gray-600">Overdue amount</p>
            @forelse ($this->overdueTotals as $currency => $amount)
                @if ($loop->first)
                    <p class="mt-1 text-xl font-semibold tabular-nums text-red-600">{{ $currency }} {{ number_format($amount, 2) }}</p>
                @else
                    <p class="text-sm text-red-600 tabular-nums">{{ $currency }} {{ number_format($amount, 2) }}</p>
                @endif
            @empty
                <p class="mt-1 text-xl font-semibold tabular-nums">0.00</p>
            @endforelse
            <p class="mt-1 text-sm text-gray-600">
                {{ $this->overdueCount }} {{ $this->overdueCount === 1 ? 'INVOICE' : 'INVOICES' }}
            </p>
        </div>

        <div class="space-y-3 rounded border p-4">
            <p class="text-sm text-gray-600">Paid this month</p>

            <div>
                <p class="text-xs uppercase tracking-wide text-gray-500">Payments received</p>
                @forelse ($this->paidThisMonth as $currency => $amount)
                    <p class="text-xl font-semibold tabular-nums">{{ $currency }} {{ number_format($amount, 2) }}</p>
                @empty
                    <p class="text-xl font-semibold tabular-nums">0.00</p>
                @endforelse
            </div>

            <div>
                <p class="text-xs uppercase tracking-wide text-gray-500">Invoices paid</p>
                <p class="text-xl font-semibold tabular-nums">{{ $this->invoicesPaidThisMonth }}</p>
            </div>
        </div>
    </div>

    <div class="space-y-2">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold">Recent invoices</h2>
            <a href="{{ route('invoices.index') }}" class="underline">View all</a>
        </div>

        <table class="w-full text-left">
            <thead>
                <tr class="border-b">
                    <th class="py-2 pr-6">Number</th>
                    <th class="py-2 pr-6">Client</th>
                    <th class="py-2 pr-6">Status</th>
                    <th class="py-2 text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->recentInvoices as $invoice)
                    <tr wire:key="recent-{{ $invoice->id }}" class="border-b">
                        <td class="py-2 pr-6">
                            <a href="{{ route('invoices.show', $invoice) }}" class="underline">{{ $invoice->number }}</a>
                        </td>
                        <td class="py-2 pr-6">{{ $invoice->client->name }}</td>
                        <td class="whitespace-nowrap py-2 pr-6">
                            @if ($invoice->isOverdue())
                                <span class="text-red-600">Overdue</span>
                            @else
                                {{ $invoice->status->label() }}
                            @endif
                        </td>
                        <td class="whitespace-nowrap py-2 text-right tabular-nums">
                            {{ $invoice->currency }} {{ number_format($invoice->total, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-4 text-gray-500">No invoices yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
