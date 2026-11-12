<?php

namespace App\Policies;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function view(User $user, Invoice $invoice): bool
    {
        return $invoice->user_id === $user->id;
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $this->view($user, $invoice) && $invoice->status === InvoiceStatus::Draft;
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $this->update($user, $invoice);
    }

    public function send(User $user, Invoice $invoice): bool
    {
        return $this->update($user, $invoice);
    }

    public function recordPayment(User $user, Invoice $invoice): bool
    {
        return $this->view($user, $invoice) && $invoice->status === InvoiceStatus::Sent;
    }
}
