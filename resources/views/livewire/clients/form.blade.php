<div class="mx-auto max-w-2xl space-y-6 p-6">
    <h1 class="text-2xl font-semibold">{{ $client ? 'Edit client' : 'New client' }}</h1>

    <form wire:submit="save" class="space-y-4">
        <div>
            <x-input-label for="name" value="Name" />
            <x-text-input id="name" type="text" class="mt-1 block w-full" wire:model="form.name" />
            <x-input-error :messages="$errors->get('form.name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" type="email" class="mt-1 block w-full" wire:model="form.email" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="phone" value="Phone" />
            <x-text-input id="phone" type="text" class="mt-1 block w-full" wire:model="form.phone" />
            <x-input-error :messages="$errors->get('form.phone')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="address" value="Address" />
            <textarea id="address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                      wire:model="form.address"></textarea>
            <x-input-error :messages="$errors->get('form.address')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button type="submit">Save client</x-primary-button>
            <a href="{{ route('clients.index') }}" class="underline">Cancel</a>
        </div>
    </form>
</div>
