<div class="mx-auto max-w-4xl space-y-6 p-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">{{ $invoice->number }}</h1>
        <div class="flex gap-3">
            @can('update', $invoice)
                <a href="{{ route('invoices.edit', $invoice) }}" class="underline">Edit</a>
            @endcan
            @can('send', $invoice)
                <button wire:click="send" wire:confirm="Send this invoice? It will be locked."
                        class="underline">Send</button>
            @endcan
            <a href="{{ route('invoices.pdf', $invoice) }}" class="underline">Download PDF</a>
            @can('delete', $invoice)
                <button wire:click="delete" wire:confirm="Delete this draft?"
                        class="text-red-600">Delete</button>
            @endcan
        </div>
    </div>

    <div class="space-y-1">
        <p>Client: <strong>{{ $invoice->client->name }}</strong></p>
        <p>Status: {{ $invoice->isOverdue() ? 'Overdue' : $invoice->status->label() }}</p>
        <p>Issued {{ $invoice->issue_date->format('d M Y') }} · Due {{ $invoice->due_date->format('d M Y') }}</p>
    </div>

    <table class="w-full text-left">
        <thead>
            <tr class="border-b">
                <th class="py-2">Description</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Price</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $item)
                <tr class="border-b">
                    <td class="py-2">{{ $item->description }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ number_format($item->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="text-right">
        <p>Subtotal {{ number_format($invoice->subtotal, 2) }}</p>
        <p>Discount −{{ number_format($invoice->discount, 2) }}</p>
        <p>Tax ({{ $invoice->tax_rate }}%) {{ number_format($invoice->tax, 2) }}</p>
        <p class="text-xl font-semibold">Total {{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</p>
        <p>Paid {{ number_format($invoice->amount_paid, 2) }} · Balance {{ number_format($invoice->balance(), 2) }}</p>
    </div>

    @if ($invoice->payments->isNotEmpty())
        <div class="space-y-2">
            <h2 class="text-lg font-semibold">Payments</h2>
            <table class="w-full text-left">
                <tbody>
                    @foreach ($invoice->payments as $payment)
                        <tr class="border-b">
                            <td class="py-2">{{ $payment->paid_on->format('d M Y') }}</td>
                            <td>{{ $payment->method }}</td>
                            <td class="text-right">{{ number_format($payment->amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @can('recordPayment', $invoice)
        <div class="space-y-2">
            <h2 class="text-lg font-semibold">Record payment</h2>
            <form wire:submit="recordPayment" class="flex flex-wrap items-start gap-2">
                <div>
                    <input type="number" step="0.01" wire:model="amount" placeholder="Amount" class="rounded">
                    @error('amount') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <input type="date" wire:model="paid_on" class="rounded">
                    @error('paid_on') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <input wire:model="method" placeholder="Method" class="rounded">
                <button type="submit" class="rounded bg-gray-900 px-4 py-2 text-white">Record payment</button>
            </form>
        </div>
    @endcan
</div>
