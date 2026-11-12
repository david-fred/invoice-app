<div class="mx-auto max-w-4xl space-y-6 p-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Clients</h1>
        <a href="{{ route('clients.create') }}" class="underline">New client</a>
    </div>

    @error('delete')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror

    <table class="w-full text-left">
        <thead>
            <tr class="border-b">
                <th class="py-2 pr-6">Name</th>
                <th class="py-2 pr-6">Email</th>
                <th class="py-2 pr-6">Phone</th>
                <th class="py-2 pr-6">Address</th>
                <th class="py-2 pr-6 text-right">Invoices</th>
                <th class="py-2"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($this->clients as $client)
                <tr wire:key="client-{{ $client->id }}" class="border-b">
                    <td class="py-2 pr-6">{{ $client->name }}</td>
                    <td class="py-2 pr-6">{{ $client->email ?: '—' }}</td>
                    <td class="whitespace-nowrap py-2 pr-6">{{ $client->phone ?: '—' }}</td>
                    <td class="py-2 pr-6" title="{{ $client->address }}">
                        {{ filled($client->address) ? \Illuminate\Support\Str::limit($client->address, 40) : '—' }}
                    </td>
                    <td class="py-2 pr-6 text-right tabular-nums">{{ $client->invoices_count }}</td>
                    <td class="whitespace-nowrap py-2 text-right">
                        <a href="{{ route('clients.edit', $client) }}" class="underline">Edit</a>
                        @can('delete', $client)
                            <button type="button" class="ms-3 text-red-600 hover:underline"
                                    wire:click="delete({{ $client->id }})"
                                    wire:confirm="Delete this client?">Delete</button>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-4 text-gray-500">No clients yet. Add your first one.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
