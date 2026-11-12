<div class="mx-auto max-w-5xl space-y-8 p-6">
    <h1 class="text-2xl font-semibold">Dashboard</h1>

    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded border p-4">
            <p class="text-sm text-gray-500">Outstanding</p>
            <p class="text-2xl font-semibold">{{ number_format($this->outstanding, 2) }}</p>
        </div>

        <div class="rounded border p-4">
            <p class="text-sm text-gray-500">Overdue invoices</p>
            <p class="text-2xl font-semibold">{{ $this->overdueCount }}</p>
        </div>

        <div class="rounded border p-4">
            <p class="text-sm text-gray-500">Paid this month</p>
            <p class="text-2xl font-semibold">{{ number_format($this->paidThisMonth, 2) }}</p>
        </div>
    </div>

    <div class="space-y-2">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold">Recent invoices</h2>
            <a href="{{ route('invoices.index') }}" class="underline">View all</a>
        </div>

        <table class="w-full text-left">
            <tbody>
                @forelse ($this->recentInvoices as $invoice)
                    <tr wire:key="recent-{{ $invoice->id }}" class="border-b">
                        <td class="py-2">
                            <a href="{{ route('invoices.show', $invoice) }}" class="underline">{{ $invoice->number }}</a>
                        </td>
                        <td>{{ $invoice->client->name }}</td>
                        <td>{{ $invoice->isOverdue() ? 'Overdue' : $invoice->status->label() }}</td>
                        <td class="text-right">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="py-4 text-gray-500">No invoices yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>