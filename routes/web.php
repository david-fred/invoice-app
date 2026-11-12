<?php

use App\Http\Controllers\DownloadInvoicePdfController;
use App\Livewire\Clients\Form as ClientForm;
use App\Livewire\Clients\Index as ClientIndex;
use App\Livewire\Dashboard;
use App\Livewire\Invoices\Form as InvoiceForm;
use App\Livewire\Invoices\Index as InvoiceIndex;
use App\Livewire\Invoices\Show as InvoiceShow;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::view('profile', 'profile')->name('profile');

    Route::get('/invoices', InvoiceIndex::class)->name('invoices.index');
    Route::get('/invoices/create', InvoiceForm::class)->name('invoices.create');
    Route::get('/invoices/{invoice}', InvoiceShow::class)->name('invoices.show');
    Route::get('/invoices/{invoice}/edit', InvoiceForm::class)->name('invoices.edit');
    Route::get('/invoices/{invoice}/pdf', DownloadInvoicePdfController::class)->name('invoices.pdf');

    Route::get('/clients', ClientIndex::class)->name('clients.index');
    Route::get('/clients/create', ClientForm::class)->name('clients.create');
    Route::get('/clients/{client}/edit', ClientForm::class)->name('clients.edit');
});

require __DIR__.'/auth.php';
