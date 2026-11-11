<?php

namespace App\Livewire\Clients;

use App\Actions\Clients\DeleteClient;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use DomainException;

class Index extends Component
{
    #[Computed]
    public function clients()
    {
        return Auth::user()->clients()->withCount('invoices')->orderBy('name')->get();
    }

    public function delete(int $clientId, DeleteClient $action): void
    {
        $client = Auth::user()->clients()->findOrFail($clientId);
        $this->authorize('delete', $client);

        try {
            $action->handle($client);
        } catch (DomainException $e) {
            $this->addError('delete', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.clients.index');
    }
}