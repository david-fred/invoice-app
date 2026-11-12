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
                <th class="py-2">Name</th>
                <th>Email</th>
                <th>Invoices</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($this->clients as $client)
                <tr wire:key="client-{{ $client->id }}" class="border-b">
                    <td class="py-2">{{ $client->name }}</td>
                    <td>{{ $client->email }}</td>
                    <td>{{ $client->invoices_count }}</td>
                    <td class="space-x-3 text-right">
                        <a href="{{ route('clients.edit', $client) }}" class="underline">Edit</a>
                        <button type="button" class="text-red-600"
                                wire:click="delete({{ $client->id }})"
                                wire:confirm="Delete this client?">Delete</button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="py-4 text-gray-500">No clients yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
