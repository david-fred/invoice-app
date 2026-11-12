<?php

namespace App\Livewire\Forms;

use App\Models\Client;
use Livewire\Form;

class ClientForm extends Form
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $address = '';

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function setClient(Client $client): void
    {
        $this->name = $client->name;
        $this->email = $client->email ?? '';
        $this->phone = $client->phone ?? '';
        $this->address = $client->address ?? '';
    }
}
