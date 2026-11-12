<div class="mx-auto max-w-4xl space-y-6 p-6">
    <h1 class="text-2xl font-semibold">{{ $invoice ? 'Edit '.$invoice->number : 'New invoice' }}</h1>

    <form wire:submit="save" class="space-y-6">
        <div class="grid gap-4 md:grid-cols-4">
            <label class="md:col-span-2">
                <span class="block text-sm text-gray-600">Client</span>
                <select wire:model="form.client_id" class="w-full rounded">
                    <option value="">Select a client</option>
                    @foreach ($this->clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                    @endforeach
                </select>
                @error('form.client_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </label>

            <label>
                <span class="block text-sm text-gray-600">Issue date</span>
                <input type="date" wire:model="form.issue_date" class="w-full rounded">
                @error('form.issue_date') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </label>

            <label>
                <span class="block text-sm text-gray-600">Due date</span>
                <input type="date" wire:model="form.due_date" class="w-full rounded">
                @error('form.due_date') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </label>
        </div>

        <div class="grid gap-4 md:grid-cols-4">
            <label>
                <span class="block text-sm text-gray-600">Tax (%)</span>
                <input type="number" step="0.01" min="0" max="100" wire:model.live="form.tax_rate" class="w-full rounded">
                @error('form.tax_rate') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </label>

            <label>
                <span class="block text-sm text-gray-600">Discount (amount)</span>
                <input type="number" step="0.01" min="0" wire:model.live="form.discount" class="w-full rounded">
                <span class="text-xs text-gray-500">Taken off the subtotal, not a percentage.</span>
                @error('form.discount') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </label>

            <label>
                <span class="block text-sm text-gray-600">Currency</span>
                <select wire:model="form.currency" class="w-full rounded">
                    @foreach (\App\Livewire\Forms\InvoiceForm::CURRENCIES as $currency)
                        <option value="{{ $currency }}">{{ $currency }}</option>
                    @endforeach
                </select>
            </label>
        </div>

        <div class="space-y-2">
            <div class="grid grid-cols-12 gap-2 text-sm text-gray-600">
                <span class="col-span-5">Description</span>
                <span class="col-span-2">Qty</span>
                <span class="col-span-2">Unit price</span>
                <span class="col-span-2 text-right">Amount</span>
                <span class="col-span-1"></span>
            </div>

            @foreach ($form->items as $index => $item)
                <div class="grid grid-cols-12 items-start gap-2" wire:key="item-{{ $index }}">
                    <div class="col-span-5">
                        <input class="w-full rounded" placeholder="e.g. Website design"
                               wire:model="form.items.{{ $index }}.description">
                        @error("form.items.{$index}.description") <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-span-2">
                        <input type="number" step="0.01" min="0" class="w-full rounded"
                               wire:model.live="form.items.{{ $index }}.quantity">
                    </div>

                    <div class="col-span-2">
                        <input type="number" step="0.01" min="0" placeholder="0.00" class="w-full rounded"
                               wire:model.live="form.items.{{ $index }}.unit_price">
                        @error("form.items.{$index}.unit_price") <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <span class="col-span-2 pt-2 text-right">{{ number_format($this->lineAmount($index), 2) }}</span>

                    <button type="button" class="col-span-1 pt-2 text-red-600"
                            wire:click="removeItem({{ $index }})">✕</button>
                </div>
            @endforeach

            <button type="button" wire:click="addItem" class="underline">+ Add item</button>
        </div>

        <label class="block">
            <span class="block text-sm text-gray-600">Notes (optional)</span>
            <textarea wire:model="form.notes" rows="3" class="w-full rounded"
                      placeholder="Payment terms, bank details, thank-you note"></textarea>
        </label>

        <div class="text-right">
            <p>Subtotal {{ number_format($this->totals['subtotal'], 2) }}</p>
            <p>Discount −{{ number_format($this->totals['discount'], 2) }}</p>
            <p>Tax ({{ $form->tax_rate ?: 0 }}%) {{ number_format($this->totals['tax'], 2) }}</p>
            <p class="text-xl font-semibold">Total {{ $form->currency }} {{ number_format($this->totals['total'], 2) }}</p>
        </div>

        <button type="submit" class="rounded bg-gray-900 px-4 py-2 text-white">Save invoice</button>
    </form>
</div>