<div class="mx-auto max-w-4xl space-y-6 p-6">
    <h1 class="text-2xl font-semibold">{{ $invoice ? 'Edit '.$invoice->number : 'New invoice' }}</h1>

    <form wire:submit="save" class="space-y-6">
        <div class="grid gap-4 md:grid-cols-4">
            <label class="md:col-span-2">Client
                <select wire:model="form.client_id" class="w-full rounded">
                    <option value="">Select a client</option>
                    @foreach ($this->clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                    @endforeach
                </select>
                @error('form.client_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </label>
            <label>Issue date <input type="date" wire:model="form.issue_date" class="w-full rounded"></label>
            <label>Due date <input type="date" wire:model="form.due_date" class="w-full rounded"></label>
            <label>Tax % <input type="number" step="0.01" wire:model.live="form.tax_rate" class="w-full rounded"></label>
            <label>Discount <input type="number" step="0.01" wire:model.live="form.discount" class="w-full rounded"></label>
        </div>

        <div class="space-y-2">
            @foreach ($form->items as $index => $item)
                <div class="grid grid-cols-12 items-center gap-2" wire:key="item-{{ $index }}">
                    <input class="col-span-5 rounded" placeholder="Description"
                           wire:model="form.items.{{ $index }}.description">
                    <input type="number" step="0.01" class="col-span-2 rounded"
                           wire:model.live="form.items.{{ $index }}.quantity">
                    <input type="number" step="0.01" class="col-span-2 rounded"
                           wire:model.live="form.items.{{ $index }}.unit_price">
                    <span class="col-span-2 text-right">{{ number_format($this->lineAmount($index), 2) }}</span>
                    <button type="button" class="col-span-1 text-red-600"
                            wire:click="removeItem({{ $index }})">✕</button>
                </div>
            @endforeach
            <button type="button" wire:click="addItem">+ Add item</button>
        </div>

        <div class="text-right">
            <p>Subtotal {{ number_format($this->totals['subtotal'], 2) }}</p>
            <p>Discount −{{ number_format($this->totals['discount'], 2) }}</p>
            <p>Tax {{ number_format($this->totals['tax'], 2) }}</p>
            <p class="text-xl font-semibold">Total {{ number_format($this->totals['total'], 2) }}</p>
        </div>

        <button type="submit" class="rounded bg-gray-900 px-4 py-2 text-white">Save invoice</button>
    </form>
</div>