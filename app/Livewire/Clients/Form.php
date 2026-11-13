<?php

namespace App\Livewire\Clients;

use App\Actions\Clients\CreateClient;
use App\Actions\Clients\UpdateClient;
use App\Livewire\Forms\ClientForm;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Form extends Component
{
    public ClientForm $form;

    public ?Client $client = null;

    public function mount(?Client $client = null): void
    {
        if ($client) {
            $this->authorize('update', $client);
            $this->client = $client;
            $this->form->setClient($client);
        }
    }

    public function save(CreateClient $create, UpdateClient $update): void
    {
        $this->form->validate();

        if ($this->client) {
            // Reload through the signed-in user's own clients, so the write never trusts the page's copy.
            $client = Auth::user()->clients()->findOrFail($this->client->id);
            $this->authorize('update', $client);

            $update->handle($client, $this->form->all());
        } else {
            $create->handle(Auth::user(), $this->form->all());
        }

        $this->redirectRoute('clients.index');
    }

    public function render()
    {
        return view('livewire.clients.form');
    }
}
